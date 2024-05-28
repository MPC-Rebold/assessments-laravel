<?php

use Livewire\Volt\Component;
use App\Services\SeedService;
use App\Models\Master;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Master $master;

    #[Validate('required|string|max:20')]
    public string $newTitle;

    public bool $renameModalOpen;

    public function openRenameModal(): void
    {
        $this->renameModalOpen = true;
    }

    public function renameMaster(): Redirector|null
    {
        $this->validate();

        try {
            SeedService::renameMaster($this->master, $this->newTitle);
        } catch (Exception $e) {
            $this->notification()->error('Failed to rename course', $e->getMessage());
            return null;
        } finally {
            $this->newTitle = '';
            $this->renameModalOpen = false;
        }
        return redirect(request()->header('Referer'));
    }

    public function mount(Master $master): void
    {
        $this->master = $master;
        $this->newTitle = '';
        $this->renameModalOpen = false;
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-lg font-bold">
            Rename Course
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
                <div class='rounded-lg border border-secondary-600 bg-secondary-50 p-4'>
                    <div class="flex items-center border-b-2 border-secondary-200 pb-3">
                        <x-icon name="information-circle" class="h-6 w-6 text-secondary-700" />
                        <div class="ml-1 text-lg text-secondary-700">
                            Rename course&nbsp;<b>{{ $master->title }}</b>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p>
                            Renaming the course will not affect connected Canvas
                            courses or associated assessments and
                            grades.
                        </p>
                    </div>
                </div>
                <div class="space-y-1">
                    <x-input class="font-mono font-bold" placeholder="New Course Title" type="text"
                        wire:model="newTitle" />
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button spinner positive label="Confirm" wire:click="renameMaster" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>
</div>
