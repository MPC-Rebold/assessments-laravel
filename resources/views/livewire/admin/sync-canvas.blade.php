<?php
use App\Models\Settings;
?>

<div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
    <div class="flex items-center justify-between">
        <div class="text-gray-500">
            Last Synced: {{ Settings::first()->last_synced_at ? Settings::first()->last_synced_at . ' PST' :  'Never'}}
        </div>
        <x-button positive spinner class="min-w-28" wire:click="syncCanvas">
            <div>
                Sync Canvas
            </div>
        </x-button>
    </div>
</div>
