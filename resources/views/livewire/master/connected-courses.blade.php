<?php

use Livewire\Volt\Component;
use App\Models\Master;
use App\Models\Course;
use App\Models\User;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Master $master;
    public array $connectedCourses;
    public array $availableCourses;

    public function mount(): void
    {
        $this->connectedCourses = $this->master->courses->pluck('title')->toArray();
        $this->availableCourses = Course::whereNull('master_id')
            ->orWhere('master_id', $this->master->id)
            ->get()
            ->pluck('title')
            ->toArray();
    }

    public function save(): void
    {
        $this->master->courses()->update(['master_id' => null]);

        $courses = Course::whereIn('title', $this->connectedCourses);
        $courses->update(['master_id' => $this->master->id]);

        $users = User::all();
        foreach ($users as $user) {
            $user->connectCourses();
        }

        $this->notification()->success('Course connections saved');
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
    <form>
        <div class="flex flex-wrap items-center justify-between gap-x-16 gap-y-4 sm:flex-nowrap">
            <h2 class="min-w-44 text-lg font-bold text-gray-800">
                Connected Courses
            </h2>
            <div class="flex w-full items-center justify-end gap-4">
                <x-select multiselect searchable class="max-w-md" wire:model="connectedCourses"
                    placeholder="No connected courses" :options="$availableCourses" />
                <div>
                    @error('title')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
                <x-button disabled positive spinner class="min-w-24 bg-slate-300 hover:bg-slate-300"
                    wire:dirty.attr.remove="disabled" wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                    wire:click="save">
                    Save
                </x-button>
            </div>
        </div>
    </form>
</div>
