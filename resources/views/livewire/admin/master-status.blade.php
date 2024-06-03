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

<a class="group flex items-center rounded-lg p-4 transition-all hover:bg-gray-200 hover:shadow"
    href="{{ route('master.edit', $masterCourse->id) }}" wire:navigate>
    <div class="flex w-1/4 items-center space-x-4">
        @if (in_array(Master::NO_SEED, $statusStrings))
            <x-icon negative name="exclamation" class="h-6 w-6 text-negative-500" />
        @elseif(in_array(Master::WARNING, $statusStrings))
            <x-icon warning name="exclamation" class="h-6 w-6 text-warning-500" />
        @elseif (in_array(Master::DISCONNECTED, $statusStrings))
            <x-icon secondary name="ban" class="h-6 w-6 text-secondary-500" />
        @elseif (in_array(Master::OKAY, $statusStrings))
            <x-icon positive name="check" class="h-6 w-6 text-positive-500" />
        @endif
        <div class="w-3/4 truncate">
            {{ $masterCourse->title }}
        </div>
    </div>
    <div class="invisible w-1/2 grow overflow-hidden text-ellipsis text-nowrap pe-4 text-gray-500 sm:visible">
        @if (in_array(Master::NO_SEED, $statusStrings))
            <div class="text-red-500">
                Missing seed
            </div>
        @elseif ($connectedCourses->isEmpty())
            No courses connected
        @else
            {{ implode(', ', $connectedCourses->pluck('title')->all()) }}
        @endif
    </div>
    <div class="flex w-1/12 justify-end transition-all group-hover:scale-105">
        <x-button.circle secondary icon="pencil" class="flex sm:hidden" />
        <x-button secondary icon="pencil" class="hidden sm:flex">
            Edit
        </x-button>
    </div>
</a>
