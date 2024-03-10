<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\Question;
use App\Services\SeedService;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Assessment $assessment;
    public int $number;
    public bool $isAdding;
    public bool $disabled;

    public string $questionText = '';
    public string $answer = '';

    public function mount(Assessment $assessment, int $number, bool $isAdding, bool $disabled): void
    {
        $this->assessment = $assessment;
        $this->number = $number;
        $this->isAdding = $isAdding;
        $this->disabled = $disabled;
    }

    public function addQuestion(): void
    {
        $this->dispatch('incrementFrom', $this->number);
        $this->isAdding = true;
    }

    public function cancelAddition(): void
    {
        $this->dispatch('incrementFrom', null);
        $this->isAdding = false;
    }

    public function saveEdition(): void
    {
        if (empty($this->questionText) || empty($this->answer)) {
            $this->notification()->error('Question and answer cannot be empty');
            return;
        }

        if (str_contains($this->questionText, "@@") || str_contains($this->answer, "@@")) {
            $this->notification()->error('Question and answer cannot contain the reserved charters "@@"');
            return;
        }

        DB::beginTransaction();

        try {
            $questions = $this->assessment->questions()->where('number', '>', $this->number)->orderBy('number', 'desc')->get();

            foreach ($questions as $question) {
                $question->update([
                    'number' => $question->number + 1,
                ]);
            }

            Question::create([
                'assessment_id' => $this->assessment->id,
                'number' => $this->number + 1,
                'question' => $this->questionText,
                'answer' => $this->answer,
            ]);


            SeedService::writeAssessment($this->assessment);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();

        $this->dispatch('incrementFrom', null);
        $this->dispatch('refresh');
        $this->isAdding = false;
        $this->notification()->success('Question added successfully');
    }
}; ?>


<div>
    @if($isAdding)
        <div class="border-2 sm:rounded-lg border-positive-400">
            <x-card>
                <x-slot name="header">
                    <div class="border-b border-gray-300 px-4 py-2 font-bold text-slate-800">
                        Question {{ $number + 1}}
                    </div>
                </x-slot>

                <div class="px-2">
                    <x-textarea wire:model="questionText" class="whitespace-nowrap font-mono font-bold" rows="6" />
                </div>

                <x-slot name="footer">
                    <div class="space-y-4">
                        <x-input wire:model="answer" class="font-mono font-bold" />
                        <div class="flex items-center justify-between">
                            <x-button secondary icon="ban" class="h-8" wire:click="cancelAddition">
                                Cancel
                            </x-button>
                            <x-button positive icon="check" class="h-8" wire:click="saveEdition">
                                Save
                            </x-button>
                        </div>
                    </div>
                </x-slot>
            </x-card>
        </div>
    @else
        @if($disabled)
            <x-button disabled spinner class="bg-white w-full h-8">
                <x-icon name="plus" class="w-4 h-4" />
                Add Question
            </x-button>
        @else
            <x-button spinner class="bg-white w-full h-8" wire:click="addQuestion">
                <x-icon name="plus" class="w-4 h-4" />
                Add Question
            </x-button>
        @endif
    @endif
</div>
