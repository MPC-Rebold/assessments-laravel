<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Question;
use App\Models\AssessmentCourse;
use App\Models\QuestionUser;
use Illuminate\Support\Collection;

new class extends Component {
    public User $user;
    public Question $question;
    public AssessmentCourse $assessment;

    public Collection $attempts;
    public bool $isCorrect;

    public function mount(User $user, Question $question, AssessmentCourse $assessment): void
    {
        $this->user = $user;
        $this->question = $question;
        $this->assessment = $assessment;

        $this->attempts = QuestionUser::where([
            'user_id' => $user->id,
            'course_id' => $this->assessment->course->id,
            'question_id' => $this->question->id,
        ])
            ->whereHas('question', function ($query) {
                $query->where('assessment_id', $this->assessment->assessment->id);
            })
            ->get()
            ->sortByDesc('created_at');

        $this->isCorrect = $this->attempts->where('is_correct', true)->isNotEmpty();
    }

    public function markCorrect(): void
    {
        QuestionUser::create([
            'user_id' => $this->user->id,
            'course_id' => $this->assessment->course->id,
            'question_id' => $this->question->id,
            'answer' => '<Marked correct by ' . auth()->user()->name . '>',
            'is_correct' => true,
        ]);

        $this->mount($this->user, $this->question, $this->assessment);
    }

    public function clearAllAttempts(): void
    {
        $this->attempts->each->delete();
        $this->mount($this->user, $this->question, $this->assessment);
    }

    public function deleteAttempt(QuestionUser $attempt): void
    {
        $attempt->delete();
        $this->mount($this->user, $this->question, $this->assessment);
    }
}; ?>

<div x-data="{ open: false }">
    <div class="bg-slate-100 shadow sm:rounded-lg">
        <button class="group w-full bg-white p-4 sm:rounded-lg sm:px-6 sm:py-4" :class="{ 'shadow': open }"
                @click="open = !open">
            <div class="flex items-center justify-between">
                <div class="flex space-x-4">
                    <div class="font-bold group-hover:underline"> Question {{ $question->number }}</div>
                    <div class="hidden text-gray-500 sm:flex">
                        Attempts: {{ $attempts->count() }}
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if ($isCorrect)
                        <x-icon name="check" class="h-5 w-5 text-positive-400" solid />
                        <div class="text-positive-400">
                            Correct
                        </div>
                    @else
                        <x-icon name="x" class="h-5 w-5 text-negative-500" solid />
                        <div class="text-negative-500">
                            Incorrect
                        </div>
                    @endif

                    <div :class="{ 'rotate-180': open }" class="transition-transform ease-in-out">
                        <x-icon name="chevron-down" class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" />
                    </div>
                </div>
            </div>
        </button>
        <div :class="{ 'max-h-0 invisible': !open, 'max-h-[999vh] py-4': open }"
             class="overflow-hidden transition-all duration-300 ease-in-out">
            <div class="space-y-2 px-4 sm:px-6">
                @if ($attempts->isEmpty())
                    <div class="text-slate-500">
                        No attempts
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div>
                            <b>Timestamp</b>
                        </div>
                        <div class="flex items-center space-x-4">
                            <b>Attempted Answer</b>
                        </div>
                    </div>
                    <hr />
                    @foreach ($attempts as $attempt)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center justify-between space-x-2">
                                <x-icon name="trash"
                                        class="h-5 w-5 text-gray-500 cursor-pointer hover:text-negative-500 transition-all"
                                        wire:click="deleteAttempt({{ $attempt->id }})" />
                                <div>
                                    {{ Carbon::parse($attempt->created_at)->format('Y-m-d H:i:s T') }}
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div>{{ $attempt->answer }}</div>
                                @if ($attempt->is_correct)
                                    <x-icon name="check" class="h-5 w-5 text-positive-400" solid />
                                @else
                                    <x-icon name="x" class="h-5 w-5 text-negative-500" solid />
                                @endif
                            </div>
                        </div>
                        @if (!$loop->last)
                            <hr />
                        @endif
                    @endforeach
                @endif
                <hr>
                <div>
                    @if(!$attempts->isEmpty())
                        <x-button negative spinner class="w-fit"
                                  wire:click="clearAllAttempts">
                            Clear All Attempts
                        </x-button>
                    @endif
                    @if(!$isCorrect)
                        <x-button positive spinner class="w-fit"
                                  wire:click="markCorrect">
                            Mark as Correct
                        </x-button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
