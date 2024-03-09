<?php

use Livewire\Volt\Component;
use App\Models\Question;
use App\Services\SeedService;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Question $question;

    public bool $isEditing = false;
    public bool $isDeleting = false;
    public string $questionText;
    public int $questionTextRows;
    public string $answer;

    public function mount(Question $question): void
    {
        $this->question = $question;
        $this->questionText = $question->question;
        $this->questionTextRows = substr_count($question->question, "\n") + 1;
        $this->answer = $question->answer;
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    public function cancelEdition(): void
    {
        $this->isEditing = false;
    }

    public function saveEdition(): void
    {
        $this->question->update([
            'question' => $this->questionText,
            'answer' => $this->answer,
        ]);

        SeedService::writeAssessment($this->question->assessment);
        $this->isEditing = false;
        $this->dispatch('refresh');

        $this->notification()->success('Question saved successfully');
    }

    public function showDeleteModal(): void
    {
        $this->isDeleting = true;
    }

    public function deleteQuestion(): void
    {
        $this->question->delete();

        $this->question->assessment
            ->questions()
            ->where('number', '>', $this->question->number)
            ->decrement('number');

        SeedService::writeAssessment($this->question->assessment);

        $this->isEditing = false;
        $this->isDeleting = false;

        $this->dispatch('refresh');
        $this->notification()->success('Question deleted successfully');
    }
}; ?>

<x-card>
    <x-slot name="header">
        <div class="border-b border-gray-300 px-4 py-2 font-bold text-slate-800">
            Question {{ $question->number }}
        </div>
    </x-slot>
    @if ($isEditing)
        <div class="px-2">
            <x-textarea wire:model="questionText" class="font-mono font-bold whitespace-nowrap	"
                        :rows="$questionTextRows" />
        </div>
    @else
        <div class="overflow-auto px-4 font-mono text-black md:px-2">
            <p class="overflow-auto whitespace-pre-wrap">{{ $question->question }}</p>
        </div>
    @endif
    <x-slot name="footer">
        @if ($isEditing)
            <div class="space-y-4">
                <x-input wire:model="answer" class="font-mono font-bold" />
                <div class="flex items-center justify-between">
                    <x-button secondary icon="ban" class="h-8" wire:click="cancelEdition">
                        Cancel
                    </x-button>
                    <div>
                        <x-button red icon="trash" class="h-8" wire:click="showDeleteModal" />
                        <x-button positive icon="check" class="h-8" wire:click="saveEdition">
                            Save
                        </x-button>
                    </div>
                </div>
            </div>
            <x-modal wire:model.defer="isDeleting">
                <x-card title="Delete Question {{ $question->number }}">
                    <div class='rounded-lg border border-negative-600 bg-negative-50 p-4'>
                        <div class="flex items-center border-b-2 border-negative-200 pb-3">
                            <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
                            <p class="ml-1 flex text-lg text-negative-600">
                                You are about to delete&nbsp;<b>Question {{ $question->number }}</b>
                            </p>
                        </div>
                        <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                            <ul class="list-disc space-y-1 text-negative-600">
                                <li>Any user answers associated with this question will be deleted</li>
                                <li>Any active assessments will have their grades changed</li>
                            </ul>
                        </div>
                    </div>

                    <x-slot name="footer">
                        <div class="flex justify-between gap-x-4">
                            <x-button flat label="Cancel" x-on:click="close" />
                            <x-button red label="Delete" wire:click="deleteQuestion" />
                        </div>
                    </x-slot>
                </x-card>
            </x-modal>
        @else
            <div class="flex items-center justify-between">
                <div class="overflow-auto font-mono">
                    {{ $question->answer }}
                </div>
                <x-button secondary icon="pencil" class="h-8" wire:click="startEditing">
                    Edit
                </x-button>
            </div>
        @endif
    </x-slot>
</x-card>
