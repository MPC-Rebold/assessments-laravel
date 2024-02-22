<?php

namespace App\Livewire\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Master;
use App\Models\Question;
use App\Models\Settings;
use App\Models\Status;
use App\Services\CanvasService;
use App\Services\SeedReaderService;
use Carbon\Carbon;
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
        $this->createMasters();
        $this->syncCourses();
        $this->syncAssessmentCourses();

        Settings::firstOrNew()->update([
            'last_synced_at' => Carbon::now('PST'),
        ]);

        $this->mount();

        $this->notification()->success(
            'Sync Complete',
        );
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
            if (! in_array($course->id, $canvasCoursesIds) && ! $course->master){
                $course->delete();
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
                    $master->status->update([
                        'missing_courses' => array_merge($master->status->missing_courses, [$masterCourseId]),
                    ]);
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
        }

        return $validStudents;
    }

    private function getValidAssessments($course): array
    {
        $validAssessments = [];
        $canvasAssignments = CanvasService::getCourseAssignments($course['id'])->json();
        foreach ($canvasAssignments as $canvasAssignment) {
            $validAssessments[] = [
                'canvas_id' => $canvasAssignment['id'],
                'title' => $canvasAssignment['name'],
                'due_at' => $canvasAssignment['due_at'],
            ];
        }

        return $validAssessments;
    }

    public function createMasters(): void
    {
        $masters = SeedReaderService::getMasters();

        foreach ($masters as $master) {
            $masterModel = Master::firstOrCreate(
                ['title' => $master]
            );

            Status::updateOrCreate(
                ['master_id' => $masterModel->id],
                [
                    'missing_assessments' => [],
                    'missing_courses' => [],
                ]
            );

            $this->createAssessments($masterModel);
        }
    }

    public function createAssessments(Master $master): void
    {
        $assessments = SeedReaderService::getAssessments($master->title);

        foreach ($assessments as $assessment) {
            $assessmentModel = Assessment::updateOrCreate(
                ['title' => $assessment, 'master_id' => $master->id]
            );

            $questions = SeedReaderService::getQuestions($master->title, $assessment);

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

                    if ($assessment_canvas_id === -1) {
                        $master->status->update([
                            'missing_assessments' => array_merge($master->status->missing_assessments, [$course->id => $assessment->id]),
                        ]);
                    }

                    AssessmentCourse::updateOrCreate(
                        ['assessment_id' => $assessment->id, 'course_id' => $course->id],
                        [
                            'assessment_canvas_id' => $assessment_canvas_id,
                            'due_at' => $due_at,
                        ]
                    );
                }
            }
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
