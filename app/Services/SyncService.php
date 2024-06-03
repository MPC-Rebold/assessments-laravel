<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Master;
use App\Models\Settings;
use App\Models\Status;
use App\Models\User;
use App\Models\UserCanvas;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Collection;
use Throwable;

class SyncService
{
    /**
     * Runs a function with protection from race conditions while syncing
     *
     * @param callable $func the function to run
     * @return void
     *
     * @throws UserException|Exception if the sync fails
     */
    public static function withOverrideProtection(callable $func): void
    {
        $settings = Settings::firstOrNew();

        if ($settings->is_syncing && Carbon::parse($settings->last_synced_at)->diffInMinutes(Carbon::now()) < 30) {
            throw new UserException('Sync is already in progress, please try again later.');
        }

        $settings->update([
            'is_syncing' => true,
        ]);

        try {
            DB::beginTransaction();

            $func();

            $settings->update([
                'last_synced_at' => Carbon::now(),
                'is_syncing' => false,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $settings->update([
                'is_syncing' => false,
            ]);

            throw $e;
        }
    }

    /**
     * Syncs the local information with the Canvas API
     *
     * @throws Exception if the sync fails
     */
    public static function sync(): void
    {
        self::withOverrideProtection(function () {
            self::validateCanvasKey();
            self::createMasters();
            self::checkMasterSeeds();
            self::checkAssessmentSeeds();
            $courses = self::syncCourses();
            self::checkMastersCourses($courses);
            self::syncAssessmentCoursesForMasters();
            self::connectUserCourses();
        });
    }

    /**
     * Syncs an updated assessment
     *
     * @throws Exception if the sync fails
     */
    public static function syncUpdatedAssessments(Collection $assessments): void
    {
        self::withOverrideProtection(function () use ($assessments) {
            self::validateCanvasKey();

            $assessments->each(function ($assessment) {
                self::syncAssessmentCoursesForAssessment($assessment);
            });
        });
    }

    /**
     * Updates the connected courses for a master
     * 1) Disconnects the previous connected courses
     * 2) Connects the new courses
     * 3) Syncs AssessmentCourses for master
     * 4) Connects the users to their enrolled/unenrolled courses
     *
     * @param Master $master the master to update connected courses for
     * @param Collection $courses the titles of the connected courses
     *
     * @throws UserException
     */
    public static function updateConnectedCourses(Master $master, Collection $courses): void
    {
        self::withOverrideProtection(function () use ($master, $courses) {
            // Disconnect previous connected courses
            $master->courses()->update(['master_id' => null]);

            // Connect new courses
            $courseTitlesToConnect = $courses->pluck('title')->toArray();
            Course::whereIn('title', $courseTitlesToConnect)->update(['master_id' => $master->id]);

            $courses = self::syncCourses();
            self::checkMastersCourses($courses);
            self::syncAssessmentCoursesForMaster($master);

            // Connect users to their enrolled/unenrolled courses
            self::connectUserCourses();
        });
    }

    /**
     * Check if the Canvas API Key is valid
     *
     * @throws UserException if the Canvas API Key is invalid
     */
    public static function validateCanvasKey(): void
    {
        if (! CanvasService::isTokenValid()) {
            throw new UserException('Invalid Canvas API Key');
        }
    }

    /**
     * Creates the masters and their assessments
     * Associates a status with each master
     *
     * @return void
     */
    public static function createMasters(): void
    {
        $masters = SeedService::getMasters();

        foreach ($masters as $master) {
            $masterModel = Master::firstOrCreate(
                ['title' => $master]
            );

            $status = Status::firstOrCreate(
                ['master_id' => $masterModel->id]
            );

            // clear missing statuses
            $status->missing_courses()->sync([]);
            $status->missing_assessments()->sync([]);
            $status->missing_assessment_seeds()->sync([]);

            self::createAssessments($masterModel);
        }
    }

    /**
     * Synchronizes the local courses with Canvas courses
     * Checks statuses for missing courses and assessments
     *
     * @return array of Canvas courses
     *
     * @throws UserException if the number of Canvas courses is invalid
     * @throws Throwable
     */
    public static function syncCourses(): array
    {
        $canvasCourses = collect(CanvasService::getCourses());

        if ($canvasCourses->isEmpty()) {
            throw new UserException('No active courses found in Canvas');
        } elseif ($canvasCourses->count() > 10) {
            throw new UserException('Too many active courses found in Canvas, please filter the courses in Canvas to 10 or less');
        }

        $validStudentsCoursesForAllCourses = self::getValidStudentsAssessments($canvasCourses->pluck('id')->toArray());

        foreach ($canvasCourses as $canvasCourse) {
            $validStudentsCourses = $validStudentsCoursesForAllCourses[$canvasCourse['id']];
            $validStudents = $validStudentsCourses['valid_students'];
            $validAssessments = $validStudentsCourses['valid_assessments'];

            Course::updateOrCreate(
                ['id' => $canvasCourse['id']],
                [
                    'title' => $canvasCourse['name'],
                    'valid_students' => $validStudents,
                    'valid_assessments' => $validAssessments,
                ]
            );
        }

        return $canvasCourses->toArray();
    }

    /**
     * Checks whether the masters have all of their connected courses in Canvas
     * Assigns missing courses to a master's status
     *
     * @param array $courses the Canvas course objects
     * @return void
     */
    public static function checkMastersCourses(array $courses): void
    {
        $masters = Master::all();
        foreach ($masters as $master) {
            $status = Status::where('master_id', $master->id)->first();

            $masterCoursesIds = $master->courses->pluck('id')->toArray();
            $canvasCoursesIds = array_column($courses, 'id');

            foreach ($masterCoursesIds as $masterCourseId) {
                if (! in_array($masterCourseId, $canvasCoursesIds)) {
                    $status->missing_courses()->attach($masterCourseId);
                }
            }
        }
    }

    /**
     * Checks whether the masters have all of their seeds
     * Assigns missing seeds to a master's status
     *
     * @return void
     */
    public static function checkMasterSeeds(): void
    {
        $masters = SeedService::getMasters();
        $dbMasters = Master::all()->pluck('title')->toArray();

        $diff = array_diff($dbMasters, $masters);

        foreach ($diff as $master) {
            $masterModel = Master::where('title', $master)->first();
            $status = Status::firstOrCreate([
                'master_id' => $masterModel->id,
            ]);
            $status->update([
                'has_seed' => false,
            ]);
        }
    }

    /**
     * Checks whether the masters have all of their connected assessments in its seed
     * Assigns missing assessments to a master's status
     *
     * @return void
     */
    public static function checkAssessmentSeeds(): void
    {
        $masters = Master::all();
        foreach ($masters as $master) {
            $status = Status::where('master_id', $master->id)->first();

            $assessments = SeedService::getAssessments($master->title);
            $dbAssessments = $master->assessments->pluck('title')->toArray();

            $diff = array_diff($dbAssessments, $assessments);

            foreach ($diff as $assessment) {
                $assessmentModel = Assessment::where('title', $assessment)->first();
                $status->missing_assessment_seeds()->attach($assessmentModel->id);
            }
        }
    }

    /**
     * Creates the Assessments and associated Questions for a master
     *
     * @param Master $master the master object
     * @return Collection of created assessments
     */
    public static function createAssessments(Master $master): Collection
    {
        $assessmentTitles = SeedService::getAssessments($master->title);

        $createdAssessments = [];

        foreach ($assessmentTitles as $assessmentTitle) {
            $assessmentModel = Assessment::updateOrCreate(
                ['title' => $assessmentTitle, 'master_id' => $master->id]
            );

            $createdAssessments[] = $assessmentModel;

            SeedService::seedQuestions($master, $assessmentModel);
        }

        return collect($createdAssessments);
    }

    /**
     * Synchronizes the AssessmentCourses for all Masters with their associated Canvas courses
     *
     * @return void
     */
    public static function syncAssessmentCoursesForMasters(): void
    {
        $masters = Master::all();

        foreach ($masters as $master) {
            self::syncAssessmentCoursesForMaster($master);
        }
    }

    /**
     * Syncs the AssessmentCourses for a Master
     *
     * @param Master $master the master to sync AssessmentCourses for
     * @return void
     */
    public static function syncAssessmentCoursesForMaster(Master $master): void
    {
        $assessments = Assessment::where('master_id', $master->id)->get();

        foreach ($assessments as $assessment) {
            self::syncAssessmentCoursesForAssessment($assessment);
        }
    }

    /**
     * Syncs the AssessmentCourses of an Assessment with the Canvas course
     * Updates the status of missing assessments
     *
     * @param Assessment $assessment the Assessment to sync
     * @return void
     */
    public static function syncAssessmentCoursesForAssessment(Assessment $assessment): void
    {
        $status = Status::where('master_id', $assessment->master->id)->first();
        $courses = $assessment->master->courses;

        $status->missing_assessments()->detach($assessment->id);

        foreach ($courses as $course) {
            $validAssessments = $course->valid_assessments;

            $assessment_canvas_id = -1;
            $due_at = null;

            foreach ($validAssessments as $validAssessment) {
                if ($assessment->title === $validAssessment['title']) {
                    $assessment_canvas_id = $validAssessment['canvas_id'];
                    $due_at = $validAssessment['due_at'];
                    break;
                }
            }

            $assessmentCourse = AssessmentCourse::updateOrCreate(
                ['assessment_id' => $assessment->id, 'course_id' => $course->id],
                [
                    'assessment_canvas_id' => $assessment_canvas_id,
                    'due_at' => $due_at,
                ]
            );

            if ($assessment_canvas_id === -1) {
                $status->missing_assessments()->attach($assessment->id, ['course_id' => $course->id]);
            } else {
                CanvasService::setMaxPoints($assessmentCourse);
            }
        }
    }

    /**
     * Connects the users to their enrolled courses
     *
     * @return void
     */
    public static function connectUserCourses(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $user->connectCourses();
        }
    }

    /**
     * @throws Throwable
     */
    private static function getValidStudentsAssessments(array $courseIds): array
    {
        $promises = [];

        foreach ($courseIds as $courseId) {
            $promises[] = CanvasService::getCourseEnrollments($courseId, true);
            $promises[] = CanvasService::getCourseAssignments($courseId, true);
        }

        $responses = Utils::unwrap($promises);

        $res = [];

        for ($i = 0; $i < count($courseIds); $i++) {
            $enrolled = $responses[$i * 2]->json();
            $validStudents = self::getValidStudents($enrolled);

            $canvasAssignments = $responses[$i * 2 + 1]->json();
            $validAssessments = self::getValidAssessments($canvasAssignments);

            $res[$courseIds[$i]] = [
                'valid_students' => $validStudents,
                'valid_assessments' => $validAssessments,
            ];
        }


        return $res;
    }

    /**
     * Returns the valid students for a Canvas course
     * Updates UserCanvas with the users
     *
     * @param array $enrolled the enrolled students response
     * @return array of valid students on the Canvas course
     */
    private static function getValidStudents(array $enrolled): array
    {
        $validStudents = [];
        foreach ($enrolled as $enrollment) {
            $validStudents[] = $enrollment['user']['login_id'];

            UserCanvas::updateOrCreate(
                ['user_email' => $enrollment['user']['login_id']],
                ['canvas_id' => $enrollment['user']['id']]
            );
        }

        return $validStudents;
    }

    /**
     * Returns the valid assessments for a Canvas course
     *
     * @param array $canvasAssignments the Canvas assignments response
     * @return array of valid assessments on the Canvas course
     */
    private static function getValidAssessments(array $canvasAssignments): array
    {
        $validAssessments = [];

        $canvasAssignmentsPublished = array_filter($canvasAssignments, function ($assignment) {
            return $assignment['published'];
        });

        foreach ($canvasAssignmentsPublished as $canvasAssignment) {
            $validAssessments[] = [
                'canvas_id' => $canvasAssignment['id'],
                'title' => $canvasAssignment['name'],
                'due_at' => $canvasAssignment['due_at'],
            ];
        }

        return $validAssessments;
    }

    /**
     * @throws Exception
     */
    public static function updateSpecificationGrading(Course $course, float $threshold): void
    {
        DB::beginTransaction();
        try {

            $specification_grading = $threshold != -1;

            $course->update([
                'specification_grading' => $specification_grading,
                'specification_grading_threshold' => $threshold,
            ]);

            $assessmentCourses = $course->assessmentCourses;
            CanvasService::regradeAssessmentCourses($assessmentCourses);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
