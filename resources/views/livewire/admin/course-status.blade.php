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
        @if (true)
            <x-button.circle warning icon="exclamation" />
        @endif
    </div>
    <div class="min-w-24 basis-2/12">
        {{ $masterCourse->title }}
    </div>
    <div class="grow overflow-hidden text-ellipsis pe-4">
        asdfasdfasdfasdfasdfasdfasdfasdfasdfasdfasdfasdfasdfasdfa
    </div>
    <div class="basis-1/12">
        <x-button icon="pencil">
            Edit
        </x-button>
    </div>
</div>
