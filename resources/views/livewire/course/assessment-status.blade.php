<?php

use App\Models\AssessmentCourse;
use Livewire\Volt\Component;
use Carbon\Carbon;

new class extends Component {
    public AssessmentCourse $assessmentCourse;

    public bool $isActive;

    public string $dueAt;

    public function mount(AssessmentCourse $assessmentCourse): void
    {
        $this->assessmentCourse = $assessmentCourse;
        $this->isActive = $assessmentCourse->is_active;
        $this->dueAt = $assessmentCourse->due_at ? Carbon::parse($assessmentCourse->due_at)->tz('PST')->format('M j, g:i A T') : 'N/A';

    }

    public function changeIsActive(): void
    {
        $this->isActive = !$this->isActive;
        $this->assessmentCourse->update(['is_active' => $this->isActive]);
    }

}; ?>

<div class="flex items-center justify-between">
    <div class="flex flex-wrap gap-6 items-center">
        <div class="flex items-center space-x-4">
            <x-canvas-button :href="'/courses/' . $assessmentCourse->course->id . '/assignments/' . $assessmentCourse->assessment_canvas_id" class="h-9 w-9" />
            <div>
                {{ $assessmentCourse->assessment->title }}
            </div>
        </div>
        <div class="text-gray-500 hidden sm:flex">
            Due at: {{ $dueAt }}
        </div>
    </div>
    <div class="flex items-center space-x-2">
        <x-button.circle sm wire:click="changeIsActive" spinner>
            @if($isActive)
                <x-icon solid name="lock-open" class="h-5 text-secondary-500" />
            @else
                <x-icon solid name="lock-closed" class="h-5 text-negative-500" />
            @endif
        </x-button.circle>
    </div>
</div>
