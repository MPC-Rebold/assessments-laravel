<?php

use App\Models\Settings;

?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:p-6">
    <div class="flex items-center justify-between">
        <div class="text-gray-500">
            Last Synced: {{ Settings::first()->last_synced_at ? Settings::first()->last_synced_at . ' PST' : 'Never' }}
        </div>
        <x-button positive spinner class="min-w-28" wire:click="syncCanvas">
            <div>
                Sync Canvas
            </div>
        </x-button>
    </div>
</div>
