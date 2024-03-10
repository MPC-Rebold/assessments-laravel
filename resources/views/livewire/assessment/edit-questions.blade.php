<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;

new class extends Component {
    public Assessment $assessment;
    public Collection $questions;

    public int|null $editingQuestion;
    public int|null $incrementFrom;

    public function mount(Assessment $assessment): void
    {
        $this->assessment = $assessment;
        $this->questions = $assessment->questions;
        $this->incrementFrom = null;
    }

    #[On('refresh')]
    public function refreshQuestions(): void
    {
        $this->questions = $this->assessment->questions;
    }

    #[On('incrementFrom')]
    public function incrementFrom(int|null $index): void
    {
        $this->incrementFrom = $index;
    }

    #[On('editQuestion')]
    public function editQuestion(int $index): void
    {
        $this->editingQuestion = $index;
    }

    #[On('cancelEdition')]
    public function cancelEdition(): void
    {
        $this->editingQuestion = null;
    }
}; ?>

<div class="space-y-4">
    @foreach ($assessment->questions as $question)
        <livewire:assessment.add-question :number="$loop->index" key="{{ now() }}"
                                          :isAdding="$loop->index === $incrementFrom"
                                          :disabled="(isset($incrementFrom) && $loop->index !== $incrementFrom) || isset($editingQuestion)"
                                          :assessment="$assessment" />

        <livewire:assessment.edit-question :question="$question"
                                           :increment="isset($incrementFrom) && $loop->index >= $incrementFrom"
                                           :disabled="isset($incrementFrom) || (isset($editingQuestion) && $loop->index !== $editingQuestion)"
                                           :isEditing="isset($editingQuestion) && $loop->index === $editingQuestion"
                                           key="{{ now() }}" />

        @if ($loop->last)
            <livewire:assessment.add-question :number="$loop->index + 1" key="{{ now() }}"
                                              :isAdding="$loop->index + 1 === $incrementFrom"
                                              :disabled="(isset($incrementFrom) && $loop->index + 1 !== $incrementFrom) || isset($editingQuestion)"
                                              :assessment="$assessment" />

        @endif
    @endforeach
</div>
