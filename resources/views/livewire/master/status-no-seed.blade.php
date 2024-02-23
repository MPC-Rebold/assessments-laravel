<?php

use Livewire\Features\SupportRedirects\Redirector;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Services\SeedService;
use App\Models\Master;
use App\Livewire\Admin\Sync;
use WireUi\Traits\Actions;
use Illuminate\Http\RedirectResponse;

new class extends Component {
    use Actions;

    public Master $master;
    public Collection $missingAssessmentSeeds;
    public bool $deleteModalOpen = false;

    public function mount(): void
    {
        $this->missingAssessmentSeeds = $this->master->status->missing_assessment_seeds;
    }

    public function restore(): void
    {
        SeedService::restore($this->master);

        $sync = new Sync();
        $sync->sync();

        $this->notification()->success('Seed course restored');

        $this->dispatch('refresh');
    }

    public function openDeleteModal(): void
    {
        $this->deleteModalOpen = true;
    }

    public function delete(): Redirector
    {
        Master::destroy($this->master->id);
        return redirect()->route('admin');
    }
}; ?>

<div class='border border-negative-600 bg-negative-50 p-4 sm:rounded-lg'>
    <div class="flex items-center border-b-2 border-negative-200 pb-3">
        <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
        <span class="ml-1 text-lg font-semibold text-negative-600">
            Missing
        </span>
    </div>
    <div class="ml-5 mt-2 flex flex-wrap items-center justify-between gap-2 pl-1">
        <ul class="list-disc space-y-1 text-negative-600">
            <li>
                No seed found for
                <b>{{ $master->title }}</b>
                @if ($missingAssessmentSeeds->isNotEmpty())
                    or its assessments
                    @foreach ($missingAssessmentSeeds as $assessment)
                        <b>{{ $assessment->title }}</b>
                        @if (!$loop->last)
                            ,&nbsp;
                        @endif
                    @endforeach
                @endif
            </li>
        </ul>
        <div class="space-x-2">
            <x-button spinner positive icon="refresh" wire:click="restore">
                Restore Seed
            </x-button>
            <x-button negative icon="trash" wire:click="openDeleteModal">
                Delete
            </x-button>
        </div>
    </div>
    <x-modal wire:model.defer="deleteModalOpen">
        <x-card title="Delete Seed Course">
            <div class='rounded-lg border border-negative-600 bg-negative-50 p-4'>
                <div class="flex items-center border-b-2 border-negative-200 pb-3">
                    <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
                    <span class="ml-1 flex text-lg text-negative-600">
                        You are about to delete&nbsp;<p class="font-bold">{{ $master->title }}</p>
                    </span>
                </div>
                <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                    <ul class="list-disc space-y-1 text-negative-600">
                        <li>All associated courses will be deleted</li>
                        <li>All associated grades will be deleted</li>
                    </ul>
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button negative spinner label="Confirm" wire:click="delete" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>
</div>
