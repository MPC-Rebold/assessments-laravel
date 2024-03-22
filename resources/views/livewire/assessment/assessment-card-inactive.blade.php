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

    public function mount(AssessmentCourse $assessmentCourse): void
    {
        $this->assessmentCourse = $assessmentCourse;
        $this->assessmentRoute = route('assessment.show', [$assessmentCourse->course->id, $assessmentCourse->assessment_canvas_id]);

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

<div
    class="group w-full overflow-hidden bg-gray-200 px-6 py-2 text-gray-900 shadow-sm sm:rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="h-14 w-14 items-center justify-center hidden sm:flex">
                <x-icon solid name="lock-closed" class="text-gray-500 h-9 w-9" />
            </div>

            <div class="py-2 text-left">
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

            <x-button secondary disabled>
                <div>Go</div>
                <x-icon solid class="h-5 w-5"
                        name="lock-closed" />
            </x-button>

        </div>
    </div>
</div>
