<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Master;
use App\Services\SeedService;
use Livewire\Attributes\Validate;
use WireUi\Traits\Actions;
use Livewire\Attributes\On;

new class extends Component {
    use Actions;

    public Collection $masterCourses;

    #[Validate('required|string|max:20')]
    public string $newMasterTitle = '';

    public bool $showInput = false;

    public function saveNewMaster(): void
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
        $this->masterCourses->push($newMaster);
    }

    #[On('syncUpdate')]
    public function updateMasters(): void
    {
        $this->mount();
    }

    public function mount(): void
    {
        $this->masterCourses = Master::all()
            ->load('courses')
            ->sortByDesc(function ($master) {
                return $master->courses->count();
            });
    }
}; ?>

<div>
    @if ($masterCourses->isEmpty())
        <div class="text-center">
            <p class="text-lg font-bold text-gray-400">
                No courses found
            </p>
        </div>
    @else
        @foreach ($masterCourses as $masterCourse)
            <livewire:admin.master-status :masterCourse="$masterCourse" key="{{ now() }}" />
            <hr />
        @endforeach
    @endif
    <div class="w-full" x-data="{ open: @entangle('showInput') }">
        <div @click="open = true" :class="open ? 'hidden' : 'block'">
            <x-button icon="plus" class="w-full rounded-t-none hover:!bg-secondary-500 hover:text-white">
                Add Course
            </x-button>
        </div>
        <div class="overflow-hidden transition-all duration-500"
            :class="{ 'max-h-0 invisible': !open, 'max-h-[100vh] p-4': open }">

            <form wire:submit="saveNewMaster">
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
</div>
