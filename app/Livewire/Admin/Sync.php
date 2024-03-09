<?php

namespace App\Livewire\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Master;
use App\Models\Question;
use App\Models\Settings;
use App\Models\Status;
use App\Models\User;
use App\Models\UserCanvas;
use App\Services\CanvasService;
use App\Services\SeedService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use WireUi\Traits\Actions;

class Sync extends Component
{
    use Actions;

    public Collection $masterCourses;

    public function sync(): void
    {
        DB::beginTransaction();

        $settings = Settings::firstOrNew();

        if ($settings->is_syncing) {
            $this->notification()->warning(
                'Scheduled syncing already in progress, please wait.',
            );

            return;
        }

        $settings->update([
            'is_syncing' => true,
        ]);

        try {
            $this->createMasters();
            $this->checkMasterSeeds();
            $this->syncCourses();
            $this->syncAssessmentCourses();
            $this->connectUserCourses();

            $settings->update([
                'last_synced_at' => Carbon::now(),
                'is_syncing' => false,
            ]);

        } catch (Exception $e) {
            $settings->update([
                'is_syncing' => false,
            ]);

            DB::rollBack();

            $this->notification()->error(
                'Sync Failed',
                $e->getMessage(),
            );

            return;
        }

        DB::commit();

        $this->mount();

        $this->notification()->success(
            'Sync Complete',
        );
    }

    public function createMasters(): void
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

            $this->createAssessments($masterModel);
        }
    }

    public function syncCourses(): void
    {
        $canvasCourses = CanvasService::getCourses()->json();

        $this->pruneCourses($canvasCourses);

        foreach ($canvasCourses as $canvasCourse) {
            $validStudents = $this->getValidStudents($canvasCourse);
            $validAssessments = $this->getValidAssessments($canvasCourse);

            Course::updateOrCreate(
                ['id' => $canvasCourse['id']],
                [
                    'title' => $canvasCourse['name'],
                    'valid_students' => $validStudents,
                    'valid_assessments' => $validAssessments,
                ]
            );
        }

        $this->checkMastersCourses($canvasCourses);
    }

    public function checkMasterSeeds(): void
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

        $this->checkAssessmentSeeds();
    }

    public function checkAssessmentSeeds(): void
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
     * Removes courses that are no longer in the Canvas and are not associated with any master
     *
     * @param array $canvasCourses
     * @return void
     */
    public function pruneCourses(array $canvasCourses): void
    {
        $canvasCoursesIds = array_column($canvasCourses, 'id');
        $courses = Course::all();

        foreach ($courses as $course) {
            if (! in_array($course->id, $canvasCoursesIds) && ! $course->master) {
                // if the course is not already marked for deletion (null), mark it for deletion
                if ($course->marked_for_deletion === null) {
                    $course->update(['marked_for_deletion' => Carbon::now()]);
                } else {
                    // if the course has been marked for deletion for more than 180 days, delete it
                    if (Carbon::parse($course->marked_for_deletion)->diffInDays(Carbon::now()) > 180) {
                        $course->delete();
                    }
                }
            }
        }
    }

    public function checkMastersCourses(array $courses): void
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

    private function getValidStudents($course): array
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

    private function getValidAssessments($course): array
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

    public function createAssessments(Master $master): void
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

    public function syncAssessmentCourses(): void
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
                        $specification = new SpecificationSetting();
                        $specification->setMaxPoints($assessmentCourse);
                    }

                }
            }
        }
    }

    public function connectUserCourses(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $user->connectCourses();
        }
    }

    public function mount(): void
    {
        $this->masterCourses = Master::all();
    }

    public function render(): View
    {
        return view('livewire.admin.sync');
    }
}
