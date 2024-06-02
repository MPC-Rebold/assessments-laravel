<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Services\SeedService;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    #[Validate('required|string|max:20')]
    public string $newMasterTitle = '';

    public bool $showInput = false;

    public function createNewMaster(): void
    {
        $this->validate();

        try {
            $newMaster = SeedService::createMaster($this->newMasterTitle);
        } catch (Exception $e) {
            $this->notification()->error('Failed to create master', $e->getMessage());

            return;
        }

        $this->notification()->success('Master created', $this->newMasterTitle);

        $this->newMasterTitle = '';
        $this->showInput = false;
        $this->dispatch('createMaster');
    }
}; ?>

<div class="w-full" x-data="{ open: @entangle('showInput') }">
    <div @click="open = true" :class="open ? 'hidden' : 'block'">
        <x-button icon="plus" class="w-full rounded-t-none hover:!bg-secondary-500 hover:text-white">
            Add Course
        </x-button>
    </div>
    <div class="overflow-hidden transition-all duration-500"
        :class="{ 'max-h-0 invisible': !open, 'max-h-[9999vh] p-4': open }">

        <form wire:submit="createNewMaster">
            @csrf
            <div class="flex items-center justify-between space-x-4">
                <x-input type="text" class="w-full" wire:model="newMasterTitle" name="new_course_title"
                    placeholder="Title" />

                <x-button positive type="submit" class="min-w-20">Submit</x-button>
            </div>
            @error('newMasterTitle')
                <div class="mt-1 text-negative-500">{{ $message }}</div>
            @enderror
        </form>
    </div>
</div>
