<?php

use App\Models\AssessmentCourse;
use Livewire\Volt\Component;
use Carbon\Carbon;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public AssessmentCourse $assessmentCourse;

    public bool $isActive;

    public string $dueAt;

    public function mount(AssessmentCourse $assessmentCourse): void
    {
        $this->assessmentCourse = $assessmentCourse;
        $this->isActive = $assessmentCourse->is_active;
        $this->dueAt = $assessmentCourse->due_at
            ? Carbon::parse($assessmentCourse->due_at)
                ->tz('PST')
                ->format('M j, g:i A T')
            : 'N/A';
    }

    public function changeIsActive(): void
    {
        $this->isActive = !$this->isActive;
        $this->assessmentCourse->update(['is_active' => $this->isActive]);
        $this->notification()->success('Assessment status updated');
    }
}; ?>

<div class="flex items-center justify-between">
    <div class="flex flex-wrap items-center gap-6">
        <div class="flex items-center space-x-4">
            <x-canvas-button :href="'/courses/' .
                $assessmentCourse->course->id .
                '/assignments/' .
                $assessmentCourse->assessment_canvas_id" class="h-9 w-9" />
            <div>
                {{ $assessmentCourse->assessment->title }}
            </div>
        </div>
        <div class="hidden text-gray-500 sm:flex">
            Due at: {{ $dueAt }}
        </div>
    </div>
    <div class="flex items-center space-x-2">
        <div class="flex items-center space-x-2">
            @if ($isActive)
                <div>Unlocked</div>
            @else
                <div>Locked</div>
            @endif

            <x-button.circle sm spinner wire:click="changeIsActive">
                @if ($isActive)
                    <x-icon solid name="lock-open" class="h-5 text-secondary-500" />
                @else
                    <x-icon solid name="lock-closed" class="h-5 text-negative-500" />
                @endif
            </x-button.circle>
        </div>
    </div>
</div>
