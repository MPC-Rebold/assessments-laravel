<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Carbon\Carbon;

new class extends Component {
    public Assessment $assessment;
    public int $courseId;
    public int $assessmentCanvasId;
    public string|null $dueAt;

    public bool $isPastDue;
    public string $assessmentRoute;
    public string $dueInString;
    public string $courseTitle;
    public int $percentage;

    public function mount(Assessment $assessment): void
    {
        $this->assessmentRoute = route('assessment.show', [$this->courseId, $this->assessmentCanvasId]);
        $this->courseTitle = Course::find($this->courseId)->title;

        if ($this->dueAt) {
            $diff = Carbon::now()->diff(Carbon::parse($this->dueAt));
            if ($diff->invert) {
                $this->dueInString = '';
            } else {
                $this->dueInString = 'Due in ' . Carbon::parse($this->dueAt)->longAbsoluteDiffForHumans(parts: 2);
            }
        } else {
            $this->dueInString = 'No due date';
        }

        $assessmentCourse = AssessmentCourse::firstWhere([
            'assessment_id' => $this->assessment->id,
            'course_id' => $this->courseId,
        ]);

        $this->percentage = $assessmentCourse->percentageForUser(auth()->user()) * 100;
        $this->isPastDue = $this->dueAt && Carbon::parse($this->dueAt)->isPast();
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
                    {{ $assessment->title }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $courseTitle }}
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <div class="hidden text-slate-500 sm:block" wire:poll.keep-alive.10s>
                {{ $dueInString }}
            </div>

            {{-- <x-canvas-button class="h-10 min-h-10 w-10 min-w-10" :href="'/courses/' . $courseId . '/assignments/' . $assessmentCanvasId" /> --}}

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
