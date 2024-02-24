<?php

use Livewire\Volt\Component;
use App\Models\AssessmentCourse;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public AssessmentCourse $assessmentCourse;

    public function submit(): void
    {
        sleep(1);
        $this->notification()->success('Submitted to Canvas');
    }
}; ?>

<div>
    <x-button positive spinner class="min-w-48" wire:click="submit">
        <p class="text-base">
            Submit to Canvas
        </p>
    </x-button>
</div>
