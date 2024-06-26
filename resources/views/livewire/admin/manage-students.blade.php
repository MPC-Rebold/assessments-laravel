<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-lg font-bold">
            Manage Users
        </div>
        <x-button secondary class="min-w-28" :href="route('user.index')" wire:navigate>
            <div class="group flex items-center space-x-2">
                <div>Manage</div>
                <div>
                    <x-icon name="chevron-right" class="h-4 w-4 transition-all ease-in-out group-hover:translate-x-1" />
                </div>
            </div>
        </x-button>
    </div>
</div>
