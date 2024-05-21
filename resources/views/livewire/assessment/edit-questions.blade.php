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

<div x-data="{ open: false }">
    <div class="bg-slate-100 shadow sm:rounded-lg">
        <button class="group w-full bg-white p-4 sm:rounded-lg sm:px-6 sm:py-5" :class="{ 'shadow': open }"
            @click="open = !open">
            <div class="flex items-center justify-between">
                <div class="text-lg font-bold">
                    Edit Questions
                </div>
                <div class="flex items-center space-x-2">
                    <div :class="{ 'rotate-180': open }" class="transition-transform ease-in-out">
                        <x-icon name="chevron-down" class="h-5 w-5 transition-all ease-in-out group-hover:scale-125" />
                    </div>
                </div>
            </div>
        </button>
        <div :class="{ 'max-h-0 invisible': !open, 'max-h-[999vh] py-4': open }"
            class="overflow-hidden transition-all duration-300 ease-in-out">
            <div class="space-y-4 p-4 sm:px-6">
                @if ($assessment->questions->isEmpty())
                    <livewire:assessment.add-question :number="0" key="{{ now() }}" :isAdding="0 === $incrementFrom"
                        :disabled="(isset($incrementFrom) && 0 !== $incrementFrom) || isset($editingQuestion)" :assessment="$assessment" />
                @else
                    @foreach ($assessment->questions as $question)
                        <livewire:assessment.add-question :number="$loop->index" key="{{ now() }}" :isAdding="$loop->index === $incrementFrom"
                            :disabled="(isset($incrementFrom) && $loop->index !== $incrementFrom) ||
                                isset($editingQuestion)" :assessment="$assessment" />

                        <livewire:assessment.edit-question :question="$question" :increment="isset($incrementFrom) && $loop->index >= $incrementFrom" :disabled="isset($incrementFrom) ||
                            (isset($editingQuestion) && $loop->index !== $editingQuestion)"
                            :isEditing="isset($editingQuestion) && $loop->index === $editingQuestion" key="{{ now() }}" />

                        @if ($loop->last)
                            <livewire:assessment.add-question :number="$loop->index + 1" key="{{ now() }}"
                                :isAdding="$loop->index + 1 === $incrementFrom" :disabled="(isset($incrementFrom) && $loop->index + 1 !== $incrementFrom) ||
                                    isset($editingQuestion)" :assessment="$assessment" />
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
