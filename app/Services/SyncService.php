<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Master;
use App\Models\Question;
use App\Models\Settings;
use App\Models\Status;
use App\Models\User;
use App\Models\UserCanvas;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Collection;

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
    public static function syncUpdatedAssessments(Master $master, array $assessmentTitles): void
    {
        self::withOverrideProtection(function () use ($master, $assessmentTitles) {
            self::validateCanvasKey();

            $createdAssessments = self::createAssessments($master);
            $assessments = $createdAssessments->whereIn('title', $assessmentTitles);

            $assessments->each(function ($assessment) {
                self::syncAssessmentCoursesForAssessment($assessment);
            });
        });
    }

    /**
     * Updates the connected courses for a master
     *
     * @throws UserException
     */
    public static function syncUpdateConnectedCourses(Master $master, array $connectedCourses): void
    {
        self::withOverrideProtection(function () use ($master, $connectedCourses) {
            $previousConnected = $master->courses->pluck('title')->toArray();

            $master->courses()->update(['master_id' => null]);

            $courses = Course::whereIn('title', $connectedCourses);
            $courses->update(['master_id' => $master->id]);

            $changedCourses = array_merge(array_diff($connectedCourses, $previousConnected), array_diff($previousConnected, $connectedCourses));

            $courses = self::syncCourses();
            self::checkMastersCourses($courses);
            self::syncAssessmentCoursesForMaster($master);

            $users = User::whereHas('courses', function ($query) use ($changedCourses) {
                $query->whereIn('title', $changedCourses);
            })->get();

            foreach ($users as $user) {
                $user->connectCourses();
            }
        });
    }

    /**
     * Check if the Canvas API Key is valid
     *
     * @throws UserException if the Canvas API Key is invalid
     */
    private static function validateCanvasKey(): void
    {
        if (CanvasService::getSelf()->status() === 401) {
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
     */
    public static function syncCourses(): array
    {
        $canvasCourses = CanvasService::getCourses();

        if (empty($canvasCourses)) {
            throw new UserException('No active courses found in Canvas');
        } elseif (count($canvasCourses) > 10) {
            throw new UserException('Too many active courses found in Canvas, please filter the courses in Canvas to 10 or less');
        }

        foreach ($canvasCourses as $canvasCourse) {
            $validStudents = self::getValidStudents($canvasCourse);
            $validAssessments = self::getValidAssessments($canvasCourse);

            Course::updateOrCreate(
                ['id' => $canvasCourse['id']],
                [
                    'title' => $canvasCourse['name'],
                    'valid_students' => $validStudents,
                    'valid_assessments' => $validAssessments,
                ]
            );
        }

        return $canvasCourses;
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

            $questions = SeedService::getQuestions($master->title, $assessmentTitle);

            foreach ($questions as $question) {
                Question::updateOrCreate(
                    ['number' => $question['number'], 'assessment_id' => $assessmentModel->id],
                    [
                        'question' => $question['question'],
                        'answer' => $question['answer'],
                    ]
                );
            }
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
     * Returns the valid students for a Canvas course
     *
     * @param array $course the Canvas course object
     * @return array of valid students on the Canvas course
     */
    private static function getValidStudents(array $course): array
    {
        $enrolled = CanvasService::getCourseEnrollments($course['id'])->json();
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
     * @param array $course the Canvas course object
     * @return array of valid assessments on the Canvas course
     */
    private static function getValidAssessments(array $course): array
    {
        $validAssessments = [];
        $canvasAssignments = CanvasService::getCourseAssignments($course['id'])->json();
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
}
