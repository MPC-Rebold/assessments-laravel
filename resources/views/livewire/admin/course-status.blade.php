<?php

use Livewire\Volt\Component;
use App\Models\Master;
use App\Models\Course;

new class extends Component {
    public Master $masterCourse;
    public array $connectedCourses;
    public array $allAvailableCourses;

    public function mount(): void
    {
        $this->allAvailableCourses = Course::all()->whereNull('master_id')->pluck('title')->toArray();
        $this->connectedCourses = $this->masterCourse->courses->all();
    }
}; ?>

<div class="flex flex-row items-center">
    <div class="min-w-16 basis-1/12">
        @if ($connectedCourses)
            <x-button.circle warning icon="exclamation" />
        @else
            <x-button.circle slate icon="ban" />
        @endif
    </div>
    <div class="min-w-24 basis-2/12">
        {{ $masterCourse->title }}
    </div>
    <div class="grow overflow-hidden text-ellipsis pe-4 text-gray-500">
        @if (empty($connectedCourses))
            - No courses connected -
        @else
            {{ implode(', ', $connectedCourses) }}
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
