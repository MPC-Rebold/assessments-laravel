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
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use WireUi\Traits\Actions;

class SyncCanvas extends Component
{
    use Actions;

    public Collection $masterCourses;


    public function syncCanvas(): void
    {
        $this->createMasters();
        $this->syncCourses();

        Settings::firstOrNew()->update([
            'last_synced_at' => Carbon::now('PST'),
        ]);

        $this->mount();

        $this->notification()->success(
            'Canvas Synced',
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
                $validAssessments[] = $canvasAssignment['name'];
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

//                        $courses = $masterModel->courses;
//
//                        foreach ($courses as $course) {
//                            $this->syncAssessments($masterModel, $course);
//                        }
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

    //    public function syncAssessments(Master $master, Course $course): void
    //    {
    //        $assessments = SeedReaderService::getAssessments($master->title);
    //        $canvasAssignments = CanvasService::getCourseAssignments($course->id)->json();
    //
    //        foreach ($assessments as $assessment) {
    //            $canvasAssignment = collect($canvasAssignments)->where('name', $assessment);
    //
    //            if ($canvasAssignment->count() > 1) {
    //                throw new Exception();
    //            } else if ($canvasAssignment->count() == 0) {
    //                throw new Exception();
    //            } else {
    //                $canvasAssignment = $canvasAssignment->first();
    //            }
    //
    //            $questions = SeedReaderService::getQuestions($master->title, $assessment);
    //
    //            $assessmentModel = Assessment::updateOrCreate(
    //                ['title' => $assessment, 'master_id' => $master->id],
    //                [
    //                    'due_at' => $canvasAssignment['due_at'],
    //                ]
    //            );
    //
    //            foreach ($questions as $question) {
    //                Question::updateOrCreate(
    //                    ['title' => $question, 'assessment_id' => $assessmentModel->id],
    //                    [
    //                        'question' => $question['question'],
    //                        'answer' => $question['answer'],
    //                        'number' => $question['number'],
    //                    ]
    //                );
    //            }
    //        }
    //    }

    public function mount(): void
    {
        $this->masterCourses = Master::all();
    }

    public function render(): View
    {
        return view('livewire.admin.sync-canvas');
    }
}
