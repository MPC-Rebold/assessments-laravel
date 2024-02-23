<?php

use App\Models\Settings;

?>

<div class="space-y-6">
    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
        <div class="flex items-center justify-between">
            <div class="text-gray-500">
                Last Synced:
                {{ Settings::first()->last_synced_at ? Settings::first()->last_synced_at . ' PST' : 'Never' }}
            </div>
            <x-button positive spinner class="min-w-24" wire:click="sync">
                Sync
            </x-button>
        </div>
    </div>
    <div class="bg-slate-100 shadow sm:rounded-lg">
        <div class="flex h-full w-full bg-white p-4 text-lg font-bold shadow sm:flex-row sm:rounded-lg sm:px-6 sm:py-4">
            <h2 class="min-w-16 basis-1/12">
                Status
            </h2>
            <h2 class="min-w-24 basis-2/12">
                Course
            </h2>
            <h2 class="grow">
                Canvas
            </h2>
            <h2 class="flex basis-1/12 justify-center">
                Edit
            </h2>
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
                    @if (!$loop->last)
                        <hr />
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>
