<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Livewire\Attributes\On;
use WireUi\Traits\Actions;
use App\Services\CanvasService;
use Illuminate\Http\Client\Response;

new class extends Component {
    use Actions;

    public AssessmentCourse $assessmentCourse;

    public Assessment $assessment;
    public Course $course;
    public Collection $questions;

    public string|null $dueAt;
    public bool $isPastDue;

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
        $this->percentage = $this->assessmentCourse->percentageForUser(auth()->user()) * 100;

        $this->isPastDue = $this->assessmentCourse->due_at !== null && Carbon::parse($this->assessmentCourse->due_at)->isPast();
    }

    #[On('refreshFooter')]
    public function refreshFooter(): void
    {
        $this->points = $this->assessmentCourse->pointsForUser(auth()->user());
        $this->percentage = ($this->points / $this->assessmentCourse->assessment->questionCount()) * 100;
    }

    public function submitToCanvas(): void
    {
        try {
            $gradeResponse = CanvasService::gradeAssessmentForUser($this->assessmentCourse, auth()->user());

            if ($gradeResponse->status() !== 200) {
                throw new Exception('Status: ' . $gradeResponse->status());
            }
        } catch (Exception $e) {
            $this->notification()->error('Failed to submit to Canvas', $e->getMessage());
            return;
        }
    }
};

?>

<div>
    @section('title', $assessment->title . ' - ' . $course->title)

    <livewire:layout.header :routes="[
        ['title' => 'Courses', 'href' => route('course.index')],
        [
            'title' => $course->title,
            'href' => route('course.show', $course->id),
        ],
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
                    Due at: {{ $dueAt ?? 'No due date' }}
                </div>
            </div>
            <hr class="border-2">
        </div>

        <livewire:assessment.instructions />
        @foreach ($questions as $question)
            <livewire:assessment.question :question="$question" :course="$course" :key="$question->id" />
        @endforeach

        <div class="flex flex-wrap items-center justify-between gap-2 px-2 sm:px-0">
            <div>
                <x-canvas-button class="h-10 w-fit" :href="'/courses/' . $course->id . '/assignments/' . $assessmentCourse->assessment_canvas_id">
                    View On Canvas
                </x-canvas-button>
            </div>
            @if (!$isPastDue)
                <div>
                    <x-button positive spinner class="min-w-48" wire:click="submitToCanvas">
                        <p class="text-base" id="submit_to_canvas">
                            Submit to Canvas
                        </p>
                    </x-button>
                </div>
            @endif
        </div>
    </div>
    @if (!$isPastDue)
        <footer class="fixed bottom-0 mx-auto w-full bg-slate-300 px-4 py-0.5 shadow-inner sm:px-6 lg:px-8">
            <div class="flex items-center justify-between pl-2">
                <div class="h-3 w-full rounded-full bg-white md:hidden">
                    <div class="h-3 rounded-full bg-positive-500 transition-all ease-out"
                        style="width: {{ $percentage }}%">
                    </div>
                </div>
                <div class="hidden w-full items-center gap-3 md:flex">
                    @foreach ($questions as $question)
                        <button
                            class="{{ $question->isCorrect(auth()->user(), $course)
                                ? 'bg-positive-500'
                                : ($question->getGuessesLeft(auth()->user(), $course) === 0
                                    ? 'bg-slate-500'
                                    : 'bg-white') }} h-3 w-full rounded-full shadow transition-all ease-in-out hover:scale-110"
                            title="Question {{ $question->number }}"
                            x-on:click="scrollToQuestion({{ $question->number }})">
                        </button>
                    @endforeach
                </div>
                <script>
                    function scrollToQuestion(questionNumber) {
                        const question = document.getElementById('question_' + questionNumber);
                        const navbarHeight = document.querySelector('nav').offsetHeight;
                        const y = question.getBoundingClientRect().top + window.scrollY -
                            navbarHeight - 20;
                        window.scrollTo({
                            top: y,
                            behavior: 'smooth'
                        });
                    }
                </script>
                <button class="ml-4 min-w-20 text-xl font-extrabold transition-all ease-in-out hover:scale-110"
                    x-data="{ percentage: false }" @click="percentage = ! percentage">
                    <div :class="{ 'hidden': !percentage }">
                        {{ $percentage }}%
                    </div>
                    <div :class="{ 'hidden': percentage }">
                        {{ $points }} /
                        {{ $assessmentCourse->assessment->questionCount() }}
                    </div>
                </button>
            </div>
        </footer>
    @else
        <footer class="fixed bottom-0 mx-auto w-full bg-positive-500 px-4 py-0 shadow-inner sm:px-6 lg:px-8">
            <div class="flex justify-center text-white">
                Practice Mode
            </div>
        </footer>
    @endif
</div>
