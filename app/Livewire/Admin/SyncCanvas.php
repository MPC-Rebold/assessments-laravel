<?php

namespace App\Livewire\Admin;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\Settings;
use App\Services\CanvasService;
use App\Services\SeedReaderService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class SyncCanvas extends Component
{
    use Actions;

    public function syncCanvas(): void
    {
        $this->syncSections();
        $this->syncCourses();

        Settings::firstOrNew()->update([
            'last_synced_at' => Carbon::now('PST'),
        ]);

        $this->notification()->success(
            'Canvas Synced',
        );
    }

    public function syncSections(): void
    {
        // Canvas 'courses' are 'sections' in our database
        $sections = CanvasService::getCourses()->json();

        foreach ($sections as $section) {
            $valid_students = [];
            $enrolled = CanvasService::getCourseEnrollments($section["id"])->json();
            foreach ($enrolled as $enrollment) {
                $valid_students[] = $enrollment["user"]["login_id"];
            }

            Course::updateOrCreate(
                ['id' => $section["id"]],
                [
                    'name' => $section["name"],
                    'valid_students' => $valid_students,
                ]
            );
        }

    }

    public function syncCourses(): void
    {
        $courses = SeedReaderService::getCourses();

        foreach ($courses as $course) {
            $course = Course::firstOrCreate(
                ['title' => $course]
            );

            $assessments = CanvasService::getSectionAssignments($course->id)->json();

            foreach ($assessments as $assessment) {
                $this->syncAssessment($course, $assessment);
            }
        }
    }

    public function syncAssessment($course, $assessment): void
    {
        if (!SeedReaderService::isValidAssessment($course->title, $assessment["name"])) {
            return;
        }

        $assessment = Assessment::updateOrCreate(
            ['id' => $assessment["id"]],
            [
                'course_id' => $course->id,
                'title' => $assessment["name"],
                'due_at' => $assessment["due_at"],
            ]
        );

        $questions = SeedReaderService::getQuestions($course->title, $assessment->title);
        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['assessment_id' => $assessment->id, 'number' => $question["number"]],
                [
                    'question' => $question["question"],
                    'answer' => $question["answer"],
                    'number' => $question["number"],
                ]
            );
        }

        if (Settings::firstOrNew()->specification_grading) {
            CanvasService::editAssignment($course->id, $assessment->id, [
                "points_possible" => 1,
            ]);
        } else {
            CanvasService::editAssignment($course->id, $assessment->id, [
                "points_possible" => $assessment->questionCount(),
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.sync-canvas');
    }
}
