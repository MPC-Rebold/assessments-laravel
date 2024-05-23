<?php

use Livewire\Volt\Component;
use App\Models\Master;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Master $masterCourse;
    public Collection $connectedCourses;
    public array $allAvailableCourses;
    public array $statusStrings;

    public function mount(): void
    {
        $this->allAvailableCourses = Course::all()->whereNull('master_id')->pluck('title')->toArray();
        $this->connectedCourses = $this->masterCourse->courses;
        $this->statusStrings = $this->masterCourse->statusStrings();
    }
}; ?>

<div class="flex flex-row items-center">
    <div class="flex basis-2/12 items-center space-x-4">
        @if (in_array('NoSeed', $statusStrings))
            <x-icon negative name="exclamation" class="h-6 w-6 text-negative-500" />
        @elseif(in_array('Warning', $statusStrings))
            <x-icon warning name="exclamation" class="h-6 w-6 text-warning-500" />
        @elseif (in_array('Disconnected', $statusStrings))
            <x-icon secondary name="ban" class="h-6 w-6 text-secondary-500" />
        @elseif (in_array('Okay', $statusStrings))
            <x-icon positive name="check" class="h-6 w-6 text-positive-500" />
        @endif
        <div class="min-w-24 basis-2/12">
            {{ $masterCourse->title }}
        </div>
    </div>

    <div class="invisible grow overflow-hidden text-ellipsis text-nowrap pe-4 text-gray-500 sm:visible sm:flex">
        @if (in_array('NoSeed', $statusStrings))
            <div class="text-red-500">
                Missing seed
            </div>
        @elseif ($connectedCourses->isEmpty())
            - No courses connected -
        @else
            {{ implode(', ', $connectedCourses->pluck('title')->all()) }}
        @endif
    </div>
    <div class="basis-1/12">
        <div class="flex justify-end">
            <x-button.circle secondary icon="pencil" class="flex md:hidden" :href="route('master.edit', $masterCourse->id)" wire:navigate />
            <x-button secondary icon="pencil" class="hidden md:flex" :href="route('master.edit', $masterCourse->id)" wire:navigate>
                Edit
            </x-button>
        </div>
    </div>
</div>
