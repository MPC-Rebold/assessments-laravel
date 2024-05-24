<?php

use App\Models\Master;

?>

<div class="space-y-4">
    <div class="space-y-4">
        @if (in_array(Master::NO_SEED, $statusStrings))
            <livewire:master.status-no-seed :master="$master" />
        @endif
        @if (in_array(Master::WARNING, $statusStrings))
            <livewire:master.status-warning :missingCourses="$missingCourses" :missingAssessments="$missingAssessments" />
        @endif
        @if (in_array(Master::DISCONNECTED, $statusStrings))
            <livewire:master.status-disconnected />
        @endif
        @if (in_array(Master::OKAY, $statusStrings))
            <livewire:master.status-successful />
        @endif

    </div>

    <div class="bg-slate-100 shadow sm:rounded-lg">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
            <form>
                <div class="flex flex-wrap items-center justify-between gap-x-16 gap-y-4 md:flex-nowrap">
                    <h2 class="min-w-60 text-lg font-bold text-gray-800">
                        Connected Canvas Courses
                    </h2>
                    <div class="flex w-full items-center justify-between gap-4 md:justify-end">
                        <x-select multiselect searchable class="max-w-md" wire:model="connectedCourses"
                            placeholder="No connected courses" :options="$availableCourses" empty-message="No available courses" />

                        <x-button disabled positive spinner class="min-w-24 bg-slate-300 hover:bg-slate-300"
                            wire:dirty.attr.remove="disabled" wire:dirty.class.remove="bg-slate-300 hover:bg-slate-300"
                            wire:click="saveConnectedCourses">
                            Save
                        </x-button>
                    </div>
                </div>
            </form>
        </div>

        @if (!$connectedCourses)
            <div class="p-4 text-center sm:px-6 sm:py-4">
                <p class="text-lg font-bold text-gray-400">
                    No Connected Courses
                </p>
            </div>
        @else
            @foreach ($connectedCourseModels as $course)
                <div class="flex items-center justify-between p-4 sm:px-6">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-4">
                            <x-canvas-button :href="'/courses/' . $course->id" class="h-9 w-9" />
                            <div class="text-lg">
                                {{ $course->title }}
                            </div>
                        </div>
                        <div class="hidden text-gray-500 sm:flex">
                            Specification Grading:
                            {{ $course->specification_grading ? "ON ($course->specification_grading_threshold)" : 'OFF' }}
                        </div>
                    </div>
                    <div>
                        <x-button secondary class="min-w-24" :href="route('course.edit', [$master->id, $course->id])" wire:navigate>
                            <div class="group flex items-center space-x-2">
                                <div>Manage</div>
                                <div>
                                    <x-icon name="chevron-right"
                                        class="h-4 w-4 transition-all ease-in-out group-hover:translate-x-1" />
                                </div>
                            </div>
                        </x-button>
                    </div>
                </div>
                @if (!$loop->last)
                    <hr />
                @endif
            @endforeach
        @endif
    </div>
</div>
