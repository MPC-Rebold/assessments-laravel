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
                <h2 class="basis-1/4">
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
        <livewire:admin.show-masters />
    </div>
</div>
