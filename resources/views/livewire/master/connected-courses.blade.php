<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Master;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\User;
use App\Livewire\Admin\Sync;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Master $master;
    public array $connectedCourses;
    public array $availableCourses;
    public string $statusString;

    public Collection $missingCourses;
    public Collection $missingAssessments;

    public function mount(): void
    {
        $this->statusString = $this->master->statusString();
        $this->connectedCourses = $this->master->courses->pluck('title')->toArray();
        $this->availableCourses = Course::whereNull('master_id')
            ->orWhere('master_id', $this->master->id)
            ->get()
            ->pluck('title')
            ->toArray();

        $this->missingCourses = $this->master->status->missing_courses;
        $this->missingAssessments = $this->master->status->missing_assessments;
    }

    public function saveConnectedCourses(): void
    {
        $this->master->courses()->update(['master_id' => null]);

        $courses = Course::whereIn('title', $this->connectedCourses);
        $courses->update(['master_id' => $this->master->id]);

        $users = User::all();
        foreach ($users as $user) {
            $user->connectCourses();
        }

        $sync = new Sync();
        $sync->sync();

        $this->mount();
        if ($this->master->statusString() === 'Okay' or $this->master->statusString() === 'Disconnected') {
            $this->notification()->success('Course connections saved');
        } else {
            $this->notification()->error('Course connections saved with errors');
        }
    }
}; ?>

<div class="space-y-4">
    <div>
        @if ($statusString === 'Okay')
            <livewire:master.status-successful />
        @elseif($statusString === 'Disconnected')
            <livewire:master.status-disconnected />
        @elseif($statusString === 'Warning')
            <livewire:master.status-warning :missingCourses="$missingCourses" :missingAssessments="$missingAssessments" />
        @elseif($statusString === 'NoSeed')
            <livewire:master.status-no-seed :master="$master" />
        @endif
    </div>
    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
        <form>
            <div class="flex flex-wrap items-center justify-between gap-x-16 gap-y-4 sm:flex-nowrap">
                <h2 class="min-w-44 text-lg font-bold text-gray-800">
                    Connected Courses
                </h2>
                <div class="flex w-full items-center justify-end gap-4">
                    <x-select multiselect searchable class="max-w-md" wire:model="connectedCourses"
                        placeholder="No connected courses" :options="$availableCourses" />

                    <x-button disabled positive spinner class="min-w-24 bg-slate-300 hover:bg-slate-300"
                        wire:dirty.attr.remove="disabled" wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                        wire:click="saveConnectedCourses">
                        Save
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</div>
