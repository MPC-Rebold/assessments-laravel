<?php

namespace App\Livewire\Admin;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Master;
use App\Models\Question;
use App\Models\Settings;
use App\Services\CanvasService;
use App\Services\SeedReaderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class SyncCanvas extends Component
{
    use Actions;

    public function syncCanvas(): void
    {
        $this->syncCourses();
        $this->syncMasters();

        Settings::firstOrNew()->update([
            'last_synced_at' => Carbon::now('PST'),
        ]);

        $this->notification()->success(
            'Canvas Synced',
        );
    }

    public function syncCourses(): void
    {
        $courses = CanvasService::getCourses()->json();

        foreach ($courses as $course) {
            $enrolled = CanvasService::getCourseEnrollments($course["id"])->json();
            $validStudents = [];
            foreach ($enrolled as $enrollment) {
                $validStudents[] = $enrollment["user"]["login_id"];
            }

            $validAssessments = [];
            $canvasAssignments = CanvasService::getCourseAssignments($course["id"])->json();
            foreach ($canvasAssignments as $canvasAssignment) {
                $validAssessments[] = $canvasAssignment["name"];
            }


            Course::updateOrCreate(
                ['id' => $course["id"]],
                [
                    'title' => $course["name"],
                    'valid_students' => $validStudents,
                    'valid_assessments' => $validAssessments,
                ]
            );
        }

    }

    public function syncMasters(): void
    {
        $masters = SeedReaderService::getMasters();

        foreach ($masters as $master) {
            $masterModel = Master::firstOrCreate(
                ['title' => $master]
            );

            $courses = $masterModel->courses;

            foreach ($courses as $course) {
                $this->syncAssessments($masterModel, $course);
            }
        }
    }

    public function syncAssessments(Master $master, Course $course): void
    {
        $assessments = SeedReaderService::getAssessments($master->title);
        $canvasAssignments = CanvasService::getCourseAssignments($course->id)->json();

        foreach ($assessments as $assessment) {
            $canvasAssignment = collect($canvasAssignments)->where('name', $assessment);

            if ($canvasAssignment->count() > 1) {
                throw new Exception();
            } else if ($canvasAssignment->count() == 0) {
                throw new Exception();
            } else {
                $canvasAssignment = $canvasAssignment->first();
            }

            $questions = SeedReaderService::getQuestions($master->title, $assessment);

            $assessmentModel = Assessment::updateOrCreate(
                ['title' => $assessment, 'master_id' => $master->id],
                [
                    'due_at' => $canvasAssignment['due_at'],
                ]
            );

            foreach ($questions as $question) {
                Question::updateOrCreate(
                    ['title' => $question, 'assessment_id' => $assessmentModel->id],
                    [
                        'question' => $question['question'],
                        'answer' => $question['answer'],
                        'number' => $question['number'],
                    ]
                );
            }
        }
    }

    public function render(): View
    {
        return view('livewire.admin.sync-canvas');
    }
}
