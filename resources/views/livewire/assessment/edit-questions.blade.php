<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;

new class extends Component {
    public Assessment $assessment;
    public Collection $questions;

    public function mount(Assessment $assessment): void
    {
        $this->assessment = $assessment;
        $this->questions = $assessment->questions;
    }

    #[On('refresh')]
    public function refreshQuestions(): void
    {
        $this->questions = $this->assessment->questions;
    }
}; ?>

<div class="space-y-6">
    @foreach ($assessment->questions as $question)
        @if($loop->first)
            <div class="absolute -ml-12 -mt-5">
                <x-button.circle icon="plus" class="bg-white" />
            </div>
        @endif
        <div>
            <livewire:assessment.edit-question :question="$question" key="{{ now() }}" />
            <div class="absolute -ml-12 -mt-1.5">
                <x-button.circle icon="plus" class="bg-white" />
            </div>
        </div>
    @endforeach
</div>
