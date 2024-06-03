<?php

use Livewire\Volt\Component;
use App\Models\Question;
use App\Services\SeedService;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Question $question;
    public bool $increment;
    public bool $disabled;
    public bool $isEditing;

    public bool $isDeleting = false;
    public string $questionText;
    public int $questionTextRows;
    public string $answer;

    public function mount(Question $question, bool $increment, bool $disabled, bool $isEditing = false): void
    {
        $this->question = $question;
        $this->disabled = $disabled;
        $this->questionText = $question->question;
        $this->questionTextRows = substr_count($question->question, "\n") + 1;
        $this->answer = $question->answer;
        $this->increment = $increment;
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
        $this->dispatch('editQuestion', $this->question->number - 1);
    }

    public function cancelEdition(): void
    {
        $this->isEditing = false;
        $this->dispatch('cancelEdition');
    }

    public function saveEdition(): void
    {
        if (empty($this->questionText) || empty($this->answer)) {
            $this->notification()->error('Question and answer cannot be empty');
            return;
        }

        if (str_contains($this->questionText, '@@') || str_contains($this->answer, '@@')) {
            $this->notification()->error('Question and answer cannot contain the reserved charters "@@"');
            return;
        }

        $this->question->update([
            'question' => $this->questionText,
            'answer' => $this->answer,
        ]);

        SeedService::writeAssessment($this->question->assessment);
        $this->isEditing = false;
        $this->dispatch('cancelEdition');
        $this->dispatch('refresh');

        $this->notification()->success('Question saved successfully');
    }

    public function showDeleteModal(): void
    {
        $this->isDeleting = true;
    }

    public function deleteQuestion(): void
    {
        DB::beginTransaction();

        try {
            $this->question->delete();

            $questionsAfter = $this->question->assessment
                ->questions()
                ->where('number', '>', $this->question->number)
                ->orderBy('number')
                ->get();

            foreach ($questionsAfter as $question) {
                $question->update(['number' => $question->number - 1]);
            }

            SeedService::writeAssessment($this->question->assessment);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        $this->isEditing = false;
        $this->isDeleting = false;

        $this->dispatch('cancelEdition');
        $this->dispatch('refresh');
        $this->notification()->success('Question deleted successfully');
    }
}; ?>

<div class="{{ $isEditing ? 'border-2 border-positive-400' : '' }} sm:rounded-lg">
    <x-card>
        <x-slot name="header">
            <div class="border-b border-gray-300 px-4 py-2 font-bold text-slate-800">
                Question {{ $question->number + ($increment ? 1 : 0) }}
            </div>
        </x-slot>
        @if ($isEditing)
            <div class="px-2">
                <x-textarea wire:model="questionText" class="whitespace-nowrap font-mono font-bold" :rows="$questionTextRows" />
            </div>
        @else
            <div class="overflow-auto px-4 font-mono text-black md:px-2">
                <p class="overflow-auto whitespace-pre-wrap text-nowrap">{{ $question->question }}</p>
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
                                    You are about to delete&nbsp;<b>Question
                                        {{ $question->number }}</b>
                                </p>
                            </div>
                            <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                                <ul class="list-disc space-y-1 text-negative-600">
                                    <li>Any user answers associated with this
                                        question will be deleted</li>
                                    <li>Any active assessments will have their
                                        grades changed</li>
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
                    <div class="overflow-auto text-nowrap font-mono">
                        {{ $question->answer }}
                    </div>
                    @if ($disabled)
                        <x-button secondary disabled icon="pencil" class="h-8">
                            Edit
                        </x-button>
                    @else
                        <x-button secondary icon="pencil" class="h-8" wire:click="startEditing">
                            Edit
                        </x-button>
                    @endif
                </div>
            @endif
        </x-slot>
    </x-card>
</div>
