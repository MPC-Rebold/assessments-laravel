<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Carbon\Carbon;

new class extends Component {
    public AssessmentCourse $assessmentCourse;

    public bool $isPastDue;
    public string $assessmentRoute;
    public string $dueInString;
    public int $percentage;

    public function mount(AssessmentCourse $assessmentCourse): void
    {
        $this->assessmentCourse = $assessmentCourse;
        $this->assessmentRoute = route('assessment.show', [$assessmentCourse->course->id, $assessmentCourse->assessment_canvas_id]);

        $this->percentage = $assessmentCourse->percentageForUser(auth()->user()) * 100;
        $this->isPastDue = $assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast();

        if ($assessmentCourse->due_at) {
            $diff = Carbon::now()->diff(Carbon::parse($assessmentCourse->due_at));

            if ($diff->invert) {
                $this->dueInString = '';
            } else {
                $this->dueInString = 'Due in ' . Carbon::parse($assessmentCourse->due_at)->longAbsoluteDiffForHumans(parts: 2);
            }
        } else {
            $this->dueInString = 'No due date';
        }
    }
}; ?>

<button
    class="group w-full overflow-hidden bg-white px-6 py-2 text-gray-900 shadow-sm transition-all hover:scale-[1.007] hover:shadow-md sm:rounded-lg"
    href="{{ $assessmentRoute }}" wire:navigate>
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <x-progress-circle class="hidden h-14 w-14 sm:block" :percentage="$percentage" :key="$percentage" />

            <div class="py-2 text-left group-hover:underline">
                <div class="text-lg font-semibold">
                    {{ $assessmentCourse->assessment->title }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $assessmentCourse->course->title }}
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <div class="hidden text-slate-500 sm:block" wire:poll.keep-alive.10s>
                {{ $dueInString }}
            </div>
            @if (!$isPastDue)
                <x-button positive :href="$assessmentRoute" wire:navigate class="relative">
                    <span class="transition-transform duration-300">Go</span>
                    <x-icon class="h-5 w-5 translate-x-0 transform transition-transform group-hover:translate-x-1"
                        name="arrow-right" />
                </x-button>
            @else
                <x-button secondary :href="$assessmentRoute" wire:navigate class="relative">
                    <span class="transition-transform duration-300">Practice</span>
                    <x-icon class="h-5 w-5 translate-x-0 transform transition-transform group-hover:translate-x-1"
                        name="arrow-right" />
                </x-button>
            @endif
        </div>
    </div>
</button>
