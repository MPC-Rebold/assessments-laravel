<?php

use Livewire\Volt\Component;
use App\Models\Settings;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public bool $specification_grading;
    public string $specification_grading_threshold;

    public function mount(): void
    {
        $this->specification_grading = Settings::sole()->specification_grading;

        if ($this->specification_grading) {
            $this->specification_grading_threshold = Settings::sole()->specification_grading_threshold * 100 . '%';
        } else {
            $this->specification_grading_threshold = 'OFF';
        }
    }

    public function updateSpecificationGrading(): void
    {
        $specification_grading = $this->specification_grading_threshold !== 'OFF';

        if ($specification_grading) {
            $specification_grading_threshold = (int)$this->specification_grading_threshold / 100;
        } else {
            $specification_grading_threshold = 0.8;  // Default Value
        }

        Settings::sole()->update([
            'specification_grading' => $specification_grading,
            'specification_grading_threshold' => $specification_grading_threshold
        ]);

        $this->specification_grading = Settings::sole()->specification_grading;

        $this->notification()->success(
            $title = 'Specification Grading Turned ' . ($specification_grading ? 'On' : 'Off'),
        );
    }

}; ?>

<div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
    <div class="flex justify-between items-center min-h-10 space-x-4">
        <div>
            <div class="text-lg font-bold ">
                Specification Grading
            </div>
            <div class="text-gray-500">
                @if($specification_grading)
                    On | Threshold: {{$specification_grading_threshold}}
                @else
                    Off
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-28">
                <x-select
                        searchable=""
                        clearable=""
                        placeholder="Threshold"
                        :options="['OFF', '65%', '70%', '75%', '80%', '85%', '90%', '95%']"
                        wire:model="specification_grading_threshold"
                />
            </div>

            <x-button
                    disabled
                    positive
                    spinner
                    class="min-w-28 bg-slate-300 hover:bg-slate-300"
                    wire:dirty.attr.remove="disabled"
                    wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                    wire:click="updateSpecificationGrading">
                Save
            </x-button>

        </div>
    </div>
</div>