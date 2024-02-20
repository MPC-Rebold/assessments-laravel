<?php

use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Services\CanvasService;
use App\Services\SeedReaderService;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Settings;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public function syncCanvas(): void
    {
        $courses = CanvasService::getCourses()->json();

        foreach ($courses as $course) {
            $this->syncCourse($course);
        }

        Settings::firstOrNew()->update([
            'last_synced_at' => Carbon::now('PST'),
        ]);

        $this->notification()->success(
            $title = 'Canvas Synced',
        );
    }

    public function syncCourse($course): void
    {
        $title = $course["name"];
        if (!SeedReaderService::isValidCourse($title)) {
            return;
        }

        $valid_students = [];
        $enrolled =  CanvasService::getCourseEnrollments($course["id"])->json();

        foreach ($enrolled as $enrollment) {
            $valid_students[] = $enrollment["user"]["login_id"];
        }

        $course = Course::updateOrCreate(
            ['id' => $course["id"]],
            [
                'title' => $title,
                'valid_students' => $valid_students,
            ]
        );

        $assessments = CanvasService::getCourseAssignments($course->id)->json();
        foreach ($assessments as $assessment) {
            $this->syncAssessment($course, $assessment);
        }
    }

    public function syncAssessment($course, $assessment): void
    {
        if (!SeedReaderService::isValidAssessment($course->title, $assessment["name"])) {
            return;
        };

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

}; ?>

<div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
    <div class="flex items-center justify-between">
        <div class="text-gray-500">
            Last Synced: {{ Settings::first()->last_synced_at ? Settings::first()->last_synced_at . ' PST' :  'Never'}}
        </div>
        <x-button positive spinner class="min-w-28" wire:click="syncCanvas">
            <div>
                Sync Canvas
            </div>
        </x-button>
    </div>
</div>
