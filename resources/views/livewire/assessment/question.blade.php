<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use App\Models\Question;
use App\Models\Course;
use App\Models\QuestionUser;
use App\Models\AssessmentCourse;
use WireUi\Traits\Actions;
use Carbon\Carbon;

new class extends Component {
    use Actions;

    public Question $question;
    public Course $course;

    public string $answer = '';
    public bool $isCorrect;
    public string $feedback;
    public int $maxAnswerLength;
    public int $guessesLeft;
    public bool $isPastDue;
    public int $dueAt;

    public function mount(): void
    {
        $this->isCorrect = $this->question->isCorrect(auth()->user(), $this->course);
        $this->guessesLeft = $this->question->getGuessesLeft(auth()->user(), $this->course);
        $this->feedback = str_repeat('_', strlen($this->question->answer));
        $this->maxAnswerLength = strlen($this->question->answer) * 2;

        $this->checkPastDue();
    }

    private function checkPastDue(): void
    {
        $assessmentCourse = AssessmentCourse::firstWhere([
            'assessment_id' => $this->question->assessment_id,
            'course_id' => $this->course->id,
        ]);

        $this->isPastDue = Carbon::parse($assessmentCourse->due_at)
            ->addSeconds(60)
            ->isPast();
    }

    public function submit(): void
    {
        if ($this->answer === '') {
            $this->notification()->warning('Answer cannot be empty');
            return;
        }

        if (strlen($this->answer) > $this->maxAnswerLength) {
            $this->notification()->error('Character limit exceeded');
            return;
        }

        if ($this->question->getGuessesLeft(auth()->user(), $this->course) <= 0) {
            $this->notification()->error('You have no more guesses left');
            return;
        }

        $this->checkPastDue();

        // Add grace-period of 1 minute
        if ($this->isPastDue) {
            $this->notification()->error('You cannot submit after the due date');
            return;
        }

        $questionUser = QuestionUser::create([
            'question_id' => $this->question->id,
            'user_id' => auth()->id(),
            'course_id' => $this->course->id,
            'answer' => $this->answer,
            'is_correct' => false,
        ]);

        $this->feedback = $questionUser->calculateFeedback();

        if (!str_contains($this->feedback, '<delete__>') && !str_contains($this->feedback, '<missing__>')) {
            $questionUser->update(['is_correct' => true]);
        }

        $this->isCorrect = $this->question->isCorrect(auth()->user(), $this->course);
        $this->guessesLeft = $this->question->getGuessesLeft(auth()->user(), $this->course);

        $this->dispatch('refreshFooter');
    }

    public function practiceSubmit(): void
    {
        $this->feedback = QuestionUser::calculateFeedbackHelper($this->answer, $this->question->answer);
    }
}; ?>

<div>
    <x-card>
        <x-slot name="header">
            <div
                class="{{ $isCorrect ? 'bg-positive-50' : ($guessesLeft === 0 ? 'bg-gray-300' : '') }} flex items-center justify-between rounded-t-md border-b border-gray-300 px-4 py-2 font-bold text-slate-800">
                <div id="question_{{ $question->number }}">Question {{ $question->number }}</div>
                @if ($isCorrect)
                    <x-icon class="h-6 w-6 text-positive-500" name="check" solid />
                @endif
            </div>
        </x-slot>
        <div class="overflow-auto px-4 font-mono text-black md:px-2">
            <p class="select-none overflow-auto whitespace-pre-wrap text-nowrap">{{ $question->question }}</p>
            <div class="mt-4 w-fit overflow-auto text-nowrap rounded-md bg-slate-200 px-2 py-1 tracking-widest">
                {!! $feedback !!}
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <div class="grow">
                    <x-input class="w-full font-mono sm:text-base" spellcheck="false" onpaste="return false;"
                        oncopy="return false;" ondrop="return false;" oncut="return false;" autocomplete="off"
                        maxlength="{{ $maxAnswerLength }}" wire:model.defer="answer" placeholder="Answer" />
                </div>

                <div class="flex w-full flex-nowrap items-center justify-between space-x-4 md:w-auto">
                    <div class="{{ $isCorrect ? 'invisible' : '' }} flex min-w-28 justify-end text-nowrap">
                        Guesses left:&nbsp;<b
                            class="{{ $guessesLeft <= 0 ? 'text-negative-500' : '' }}">{{ $guessesLeft }}</b>
                    </div>
                    <div>
                        @if ($isPastDue)
                            <x-button positive spinner class="min-w-28" wire:click="practiceSubmit">
                                Check
                            </x-button>
                        @elseif ($isCorrect)
                            <x-button positive spinner class="min-w-28" wire:click="practiceSubmit">
                                <x-icon class="h-5 w-5" name="check" solid />
                                Submit
                            </x-button>
                        @elseif ($guessesLeft > 0)
                            <x-button secondary spinner class="group min-w-28" wire:click="submit">
                                <div class="-me-4 transition-all ease-in-out" wire:dirty.class="group-hover:-me-1">
                                    Submit
                                </div>
                                <x-icon class="invisible -me-3 h-5 w-5 scale-0 transition-all ease-in-out"
                                    wire:dirty.class="group-hover:visible group-hover:scale-100" name="chevron-right"
                                    solid />
                            </x-button>
                        @else
                            <x-button secondary disabled class="min-w-28">
                                <x-icon class="h-5 w-5" name="ban" solid />
                                Submit
                            </x-button>
                        @endif
                    </div>
                </div>
            </div>
        </x-slot>
    </x-card>
</div>
