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
                $this->dueInString = $diff->d === 1 ? '1 day' : ($diff->d > 1 ? $diff->d . ' days' : '') . ($diff->h ? ($diff->h === 1 ? '1 hour' : $diff->h . ' hours') : ($diff->i === 1 ? $diff->i . ' minute' : $diff->i . ' minutes'));
            }
        } else {
            $this->dueInString = 'No due date';
        }

        $assessmentCourse = AssessmentCourse::firstWhere([
            'assessment_id' => $this->assessment->id,
            'course_id' => $this->courseId,
        ]);

        $this->percentage = ($assessmentCourse->pointsForUser(auth()->user()) / $this->assessment->questionCount()) * 100;
    }
}; ?>

<div class="overflow-hidden bg-white px-6 py-2 text-gray-900 shadow-sm transition-all hover:scale-[1.007] sm:rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <x-progress-circle class="hidden h-14 w-14 sm:block" :percentage="$percentage" :key="$percentage" />

            <div class="flex items-center space-x-4 py-2">
                <a class="hover:underline" href="{{ $assessmentRoute }}" wire:navigate>
                    <div>
                        <div class="text-lg font-semibold">
                            {{ $assessment->title }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $courseTitle }}
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <div class="hidden text-slate-500 sm:block" wire:poll.keep-alive wire:poll.15s>
                {{ $dueInString }}
            </div>

            <x-canvas-button class="h-10 min-h-10 w-10 min-w-10" :href="'/courses/' . $courseId . '/assignments/' . $assessmentCanvasId" />

            <x-button secondary :href="$assessmentRoute" wire:navigate class="relative">
                <span class="transition-transform duration-300">Go</span>
                <x-icon class="h-5 w-5 translate-x-0 transform transition-transform group-hover:translate-x-1"
                    name="arrow-right" />
            </x-button>
        </div>
    </div>
</div>
