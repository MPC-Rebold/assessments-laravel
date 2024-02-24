<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Livewire\Attributes\On;

new class extends Component {
    public AssessmentCourse $assessmentCourse;

    public Assessment $assessment;
    public Course $course;
    public Collection $questions;

    public string|null $dueAt;

    public int $percentage;
    public int $points;

    public function mount(): void
    {
        $this->assessment_canvas_id = request()->route('assessmentId');
        $this->assessmentCourse = AssessmentCourse::firstWhere('assessment_canvas_id', $this->assessment_canvas_id);

        $this->assessment = $this->assessmentCourse->assessment;
        $this->course = $this->assessmentCourse->course;
        $this->questions = $this->assessment->questions->sortBy('number');

        if ($this->assessmentCourse->due_at) {
            $this->dueAt = Carbon::parse($this->assessmentCourse->due_at)
                ->setTimezone('PST')
                ->format('M j, g:i A T');
        } else {
            $this->dueAt = null;
        }

        $this->assessment_canvas_id = request()->route('assessmentId');

        $this->points = $this->assessmentCourse->pointsForUser(auth()->user());
        $this->percentage = ($this->points / $this->assessmentCourse->assessment->questionCount()) * 100;
    }

    #[On('refreshFooter')]
    public function refreshFooter(): void
    {
        $this->points = $this->assessmentCourse->pointsForUser(auth()->user());
        $this->percentage = ($this->points / $this->assessmentCourse->assessment->questionCount()) * 100;
    }
};

?>

<div>
    @section('title', $assessment->title . ' - ' . $course->title)

    <livewire:layout.header :routes="[
        ['title' => 'Courses', 'href' => route('course.index')],
        ['title' => $course->title, 'href' => route('course.show', $course->id)],
        [
            'title' => $assessment->title,
            'href' => route('assessment.show', [$course->id, $assessmentCourse->assessment_canvas_id]),
        ],
    ]" />

    <div class="mx-auto max-w-7xl space-y-6 pb-20 pt-10 sm:px-6 lg:px-8">
        <div class="sm: space-y-4 px-2">
            <div class="flex flex-wrap items-baseline justify-between gap-2 text-nowrap">
                <h1 class="text-2xl">{{ $assessment->title }}</h1>
                <div class="flex items-baseline text-slate-800">
                    Due at: {{ $dueAt ?? 'N/A' }}
                </div>
            </div>
            <hr class="border-2">
        </div>

        <livewire:assessment.instructions />
        @foreach ($questions as $question)
            <livewire:assessment.question :question="$question" :course="$course" :key="$question->id" />
        @endforeach

        <div class="mt-1 flex items-center justify-between px-2 sm:px-0">
            <div>
                <x-canvas-button class="h-10 w-fit" :href="'/courses/' . $course->id . '/assignments/' . $assessmentCourse->assessment_canvas_id">
                    <div class="ms-2 text-nowrap text-base font-extrabold">
                        Canvas
                    </div>
                </x-canvas-button>
            </div>
            <div>
                <x-button positive>
                    <p class="text-base">
                        Submit to Canvas
                    </p>
                </x-button>
            </div>
        </div>
    </div>

    <footer class="fixed bottom-0 mx-auto w-full bg-slate-200 px-4 py-2 shadow-inner sm:px-6 lg:px-8">
        <div class="flex items-center justify-between pl-2">
            <div class="h-2.5 w-full rounded-full bg-white dark:bg-gray-700">
                <div class="h-2.5 rounded-full bg-positive-500 transition-all ease-out"
                    style="width: {{ $percentage }}%">
                </div>
            </div>
            <button class="ml-4 min-w-20 text-xl font-extrabold transition-all ease-in-out hover:scale-125"
                x-data="{ percentage: false }" @click="percentage = ! percentage">
                <div :class="{ 'hidden': !percentage }">
                    {{ $percentage }}%
                </div>
                <div :class="{ 'hidden': percentage }">
                    {{ $points }} / {{ $assessmentCourse->assessment->questionCount() }}
                </div>
            </button>
        </div>
    </footer>
</div>
