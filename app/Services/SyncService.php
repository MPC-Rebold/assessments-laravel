<?php

namespace App\Services;

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

class SyncService
{
    /**
     * Syncs the local information with the Canvas API
     *
     * @throws Exception if the sync fails
     */
    public static function sync(): void
    {
        $settings = Settings::firstOrNew();

        if ($settings->is_syncing && Carbon::parse($settings->last_synced_at)->diffInMinutes(Carbon::now()) < 30) {
            throw new Exception('Sync is already in progress, please try again later.');
        }

        $settings->update([
            'is_syncing' => true,
        ]);

        try {
            DB::beginTransaction();

            self::validateCanvasKey();
            self::createMasters();
            self::checkMasterSeeds();
            self::syncCourses();
            self::syncAssessmentCourses();
            self::connectUserCourses();

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
     * Check if the Canvas API Key is valid
     *
     * @throws Exception if the Canvas API Key is invalid
     */
    private static function validateCanvasKey(): void
    {
        if (CanvasService::getSelf()->status() === 401) {
            throw new Exception('Invalid Canvas API Key');
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
     * @return void
     *
     * @throws Exception if the number of Canvas courses is invalid
     */
    public static function syncCourses(): void
    {
        $canvasCourses = CanvasService::getCourses();

        if (empty($canvasCourses)) {
            throw new Exception('No active courses found in Canvas');
        } elseif (count($canvasCourses) > 10) {
            throw new Exception('Too many active courses found in Canvas, please filter the courses in Canvas to 10 or less');
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

        self::checkMastersCourses($canvasCourses);
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
            $masterCoursesIds = $master->courses->pluck('id')->toArray();

            $canvasCoursesIds = array_column($courses, 'id');

            foreach ($masterCoursesIds as $masterCourseId) {
                if (! in_array($masterCourseId, $canvasCoursesIds)) {
                    $status = Status::where('master_id', $master->id)->first();
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

        self::checkAssessmentSeeds();
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
            $assessments = SeedService::getAssessments($master->title);
            $dbAssessments = $master->assessments->pluck('title')->toArray();

            $diff = array_diff($dbAssessments, $assessments);

            foreach ($diff as $assessment) {
                $assessmentModel = Assessment::where('title', $assessment)->first();
                $status = Status::where('master_id', $master->id)->first();
                $status->missing_assessment_seeds()->attach($assessmentModel->id);
            }
        }
    }

    /**
     * Creates the Assessments and associated Questions for a master
     *
     * @param Master $master the master object
     * @return void
     */
    public static function createAssessments(Master $master): void
    {
        $assessments = SeedService::getAssessments($master->title);

        foreach ($assessments as $assessment) {
            $assessmentModel = Assessment::updateOrCreate(
                ['title' => $assessment, 'master_id' => $master->id]
            );

            $questions = SeedService::getQuestions($master->title, $assessment);

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
    }

    /**
     * Synchronizes the AssessmentCourses with their associated Canvas courses
     *
     * @return void
     */
    public static function syncAssessmentCourses(): void
    {
        $masters = Master::all();

        foreach ($masters as $master) {
            $courses = $master->courses;

            foreach ($courses as $course) {
                $assessments = Assessment::where('master_id', $master->id)->get();
                $validAssessments = $course->valid_assessments;

                foreach ($assessments as $assessment) {
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
                        $status = Status::where('master_id', $master->id)->first();
                        $status->missing_assessments()->attach($assessment->id, ['course_id' => $course->id]);
                    } else {
                        CanvasService::setMaxPoints($assessmentCourse);
                    }

                }
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