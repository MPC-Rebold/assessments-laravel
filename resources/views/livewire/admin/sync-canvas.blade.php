<?php

use Livewire\Volt\Component;
use App\Services\CanvasService;
use App\Services\SeedReaderService;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Settings;

new class extends Component {
    public array $courses = [];

    public function syncCanvas(): void
    {
        $this->syncCourses();
    }

    private function syncCourses(): void
    {
        $canvasApi = new CanvasService();
        $this->courses = $canvasApi->getCourses()->json();

        foreach ($this->courses as $course) {
            $title = $course["name"];
            if (!SeedReaderService::isValidCourse($title)) {
                continue;
            }

            $valid_students = [];
            $enrolled = $canvasApi->getCourseEnrollments($course["id"])->json();

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

            $assessments = $canvasApi->getCourseAssignments($course->id)->json();
            foreach ($assessments as $assessment) {
                if (!SeedReaderService::isValidAssessment($course->title, $assessment["name"])) {
                    continue;
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

                $settings = Settings::firstOrNew();

                if ($settings->specification_grading) {
                    $canvasApi->editAssignment($course->id, $assessment->id, [
                        "points_possible" => 1,
                    ]);
                } else {
                    $canvasApi->editAssignment($course->id, $assessment->id, [
                        "points_possible" => $assessment->questionCount(),
                    ]);
                }
            }
        }
    }

}; ?>

<div>
    <ul>
        @foreach($courses as $course)
            <li>{{ $course["name"] }}</li>
            <li>{{ \Carbon\Carbon::now() }}</li>
        @endforeach
    </ul>
    <x-button primary wire:click="syncCanvas">
        Sync Canvas
    </x-button>
</div>
