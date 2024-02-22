<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Services\SeedService;
use App\Models\Master;
use WireUi\Traits\Actions;

new class extends Component {
    use Actions;

    public Master $master;

    public function restore(): void
    {
        SeedService::restore($this->master);
        $this->notification()->success('Seed course restored');
    }
}; ?>

<div class='border border-negative-600 bg-negative-50 p-4 sm:rounded-lg'>
    <div class="flex items-center border-b-2 border-negative-200 pb-3">
        <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
        <span class="ml-1 text-lg font-semibold text-negative-600">
            Warning
        </span>
    </div>
    <div class="ml-5 mt-2 flex items-center justify-between pl-1">
        <ul class="list-disc space-y-1 text-negative-600">
            <li>
                <div class="flex overflow-auto text-nowrap">
                    <p>No Seed course found at&nbsp;</p>
                    <p class="font-bold">/database/{{ $master->title }} </p>
                </div>
            </li>
        </ul>
        <div class="space-x-2">
            <x-button positive icon="refresh" wire:click="restore">
                Restore
            </x-button>
            <x-button negative icon="trash">
                Delete
            </x-button>
        </div>
    </div>
</div>
