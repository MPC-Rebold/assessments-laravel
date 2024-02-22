<?php

namespace App\Livewire\Admin;

use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Master;
use App\Models\Question;
use App\Models\Settings;
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
        $this->synAssessmentCourses();

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
        $courses = CanvasService::getCourses()->json();

        foreach ($courses as $course) {
            $enrolled = CanvasService::getCourseEnrollments($course['id'])->json();
            $validStudents = [];
            foreach ($enrolled as $enrollment) {
                $validStudents[] = $enrollment['user']['login_id'];
            }

            $validAssessments = [];
            $canvasAssignments = CanvasService::getCourseAssignments($course['id'])->json();
            foreach ($canvasAssignments as $canvasAssignment) {
                $validAssessments[] = [
                    'canvas_id' => $canvasAssignment['id'],
                    'title' => $canvasAssignment['name'],
                    'due_at' => $canvasAssignment['due_at'],
                ];
            }

            Course::updateOrCreate(
                ['id' => $course['id']],
                [
                    'title' => $course['name'],
                    'valid_students' => $validStudents,
                    'valid_assessments' => $validAssessments,
                ]
            );
        }

    }

    public function createMasters(): void
    {
        $masters = SeedReaderService::getMasters();

        foreach ($masters as $master) {
            $masterModel = Master::firstOrCreate(
                ['title' => $master]
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

    public function synAssessmentCourses(): void
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
