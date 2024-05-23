<?php

use Carbon\Carbon;

?>

<div class="space-y-6">
    <div class="bg-slate-100 shadow sm:rounded-lg">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-lg font-bold">
                    Sync
                </div>
                <x-button positive spinner class="min-w-28" wire:click="sync">
                    Sync
                </x-button>
            </div>
        </div>
        <livewire:admin.sync-details lazy="true" />
    </div>
    <div class="bg-slate-100 shadow sm:rounded-lg">
        <div class="flex bg-white p-4 text-lg font-bold shadow sm:flex-row sm:rounded-lg sm:px-6 sm:py-4">
            <div class="hidden h-full w-full md:flex">
                <h2 class="min-w-32 basis-2/12">
                    Local Course
                </h2>
                <h2 class="grow">
                    Connected Canvas Courses
                </h2>
                <h2 class="flex basis-1/12 justify-center">
                    Edit
                </h2>
            </div>
            <div class="block md:hidden">
                <h2>
                    Courses
                </h2>
            </div>
        </div>
        <div class="space-y-4 p-4 sm:p-6">
            @if ($masterCourses->isEmpty())
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-400">
                        No courses found
                    </p>
                </div>
            @else
                @foreach ($masterCourses as $masterCourse)
                    <livewire:admin.course-status :masterCourse="$masterCourse" key="{{ now() }}" />
                    <hr />
                @endforeach
            @endif
            <div class="w-full" x-data="{ open: @entangle('showInput') }">
                <div @click="open = true" :class="open ? 'hidden' : 'block'">
                    <x-button icon="plus" class="w-full hover:bg-secondary-500 hover:text-white">
                        Add Course
                    </x-button>
                </div>
                <div class="overflow-hidden transition-all duration-500"
                    :class="{ 'max-h-0 invisible': !open, 'max-h-[100vh]': open }">

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
    </div>
</div>
