<?php

use Livewire\Volt\Component;
use App\Models\Master;
use App\Services\SeedService;

new class extends Component {
    public Master $master;

    public bool $deleteModalOpen = false;
    public string $confirmDeleteString = '';
    public bool $deleteConfirmed = false;

    public function mount(Master $master): void
    {
        $this->master = $master;
    }

    public function updated(): void
    {
        $this->deleteConfirmed = trim($this->confirmDeleteString) === $this->master->title;
    }

    public function openDeleteModal(): void
    {
        $this->deleteModalOpen = true;
    }

    public function deleteMaster(): void
    {
        SeedService::deleteMaster($this->master);
        $this->redirect(route('admin'));
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-lg font-bold text-negative-500">
            Delete Course
        </div>
        <x-button negative class="w-28" wire:click="openDeleteModal">
            <div class="flex items-center space-x-2">
                <x-icon solid name="trash" class="h-4 w-4" />
                <div>Delete</div>
            </div>
        </x-button>
    </div>
    <x-modal wire:model.defer="deleteModalOpen">
        <x-card title="Delete Course">
            <div class="space-y-2">
                <div class='rounded-lg border border-negative-600 bg-negative-50 p-4'>
                    <div class="flex items-center border-b-2 border-negative-200 pb-3">
                        <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
                        <span class="ml-1 text-lg text-negative-600">
                            You are about to delete the
                            course&nbsp;<b>{{ $master->title }}</b>
                        </span>
                    </div>
                    <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                        <ul class="list-disc space-y-1 text-negative-600">
                            <li>All associated assessments will be deleted</li>
                            <li>All associated grades will be deleted</li>
                            <li>This will not affect Canvas</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>
                </div>
                <div class="space-y-1">
                    <div>
                        Type "<b>{{ $master->title }}</b>" below to confirm
                    </div>
                    <x-input class="font-mono font-bold" placeholder="{{ $master->title }}"
                        wire:model.live="confirmDeleteString" />
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button spinner label="Confirm" wire:click="deleteMaster" :disabled="!$deleteConfirmed" :secondary="!$deleteConfirmed"
                        :negative="$deleteConfirmed" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>
</div>
