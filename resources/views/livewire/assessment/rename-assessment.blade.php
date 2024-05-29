<?php

use App\Services\SeedService;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\Volt\Component;
use App\Models\Assessment;
use WireUi\Traits\Actions;
use App\Services\SyncService;

new class extends Component {
    use Actions;

    public Assessment $assessment;

    #[Validate('required|string|max:50')]
    public string $newTitle;

    public bool $renameModalOpen;

    public function openRenameModal(): void
    {
        $this->renameModalOpen = true;
    }

    public function renameAssessment(): Redirector|null
    {
        $this->validate();

        try {
            $updatedAssessment = SeedService::renameAssessment($this->assessment, $this->newTitle);
        } catch (Exception $e) {
            $this->notification()->error('Failed to rename assessment', $e->getMessage());
            return null;
        } finally {
            $this->newTitle = '';
            $this->renameModalOpen = false;
        }

        try {
            SyncService::syncUpdatedAssessments($this->assessment->master, [$updatedAssessment->title]);
        } catch (Exception $e) {
            $this->notification()->error('Failed to sync assessment', $e->getMessage());
            return null;
        }

        return redirect(request()->header('Referer'));
    }

    public function mount(Assessment $assessment): void
    {
        $this->assessment = $assessment;
        $this->newTitle = '';
        $this->renameModalOpen = false;
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-lg font-bold">
            Rename Assessment
        </div>
        <x-button secondary class="w-28" wire:click="openRenameModal">
            <div class="flex items-center space-x-2">
                <x-icon solid name="pencil" class="h-4 w-4" />
                <div>Rename</div>
            </div>
        </x-button>
    </div>
    <x-modal wire:model.defer="renameModalOpen">
        <x-card title="Rename Course">
            <div class="space-y-2">
                <div class='rounded-lg border border-warning-600 bg-warning-50 p-4'>
                    <div class="flex items-center border-b-2 border-warning-200 pb-3">
                        <x-icon name="exclamation" class="h-6 w-6 text-warning-700" />
                        <div class="ml-1 text-lg text-warning-700">
                            Rename
                            assessment&nbsp;<b>{{ $assessment->title }}</b> on
                            course&nbsp;<b>{{ $assessment->master->title }}</b>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-warning-700">
                            Renaming the Assessment may disconnect from any
                            associated Canvas assessments.
                        </p>
                    </div>
                </div>
                <div class="space-y-1">
                    <x-input class="font-mono font-bold" placeholder="New Assessment Title" type="text"
                        wire:model="newTitle" />
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button spinner positive label="Confirm" wire:click="renameAssessment" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>
</div>
