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
    public bool $isMissingMaster;

    public bool $deleteMasterModalOpen = false;
    public bool $deleteAssessmentsModalOpen = false;

    public function mount(): void
    {
        $this->isMissingMaster = !SeedService::isValidMaster($this->master->title);
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

    public function openDeleteMasterModal(): void
    {
        $this->deleteMasterModalOpen = true;
    }

    public function openDeleteAssessmentsModal(): void
    {
        $this->deleteAssessmentsModalOpen = true;
    }

    public function deleteMaster(): Redirector
    {
        Master::destroy($this->master->id);
        return redirect()->route('admin');
    }

    public function deleteAssessments(): Redirector
    {
        foreach ($this->missingAssessmentSeeds as $assessment) {
            $assessment->delete();
        }

        return redirect(request()->header('Referer'));
    }
}; ?>

<div class='border-y border-negative-600 bg-negative-50 p-4 sm:rounded-lg sm:border-x'>
    <div class="flex items-center border-b-2 border-negative-200 pb-3">
        <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
        <span class="ml-1 text-lg font-semibold text-negative-600">
            Missing
        </span>
    </div>
    <div class="ml-5 mt-2 flex flex-wrap items-center justify-between gap-2 pl-1">
        <ul class="list-disc space-y-1 text-negative-600">
            <li>
                @if ($isMissingMaster)
                    No seed found for
                    <b>{{ $master->title }}</b>
                @else
                    No seed found for assessments:
                    <b>{{ $missingAssessmentSeeds->pluck('title')->implode(', ') }}</b>
                @endif
            </li>
        </ul>
        <div class="flex flex-wrap gap-2">
            @if ($isMissingMaster)
                <x-button spinner positive icon="refresh" wire:click="restore">
                    Restore Course
                </x-button>
                <x-button negative icon="trash" wire:click="openDeleteMasterModal">
                    Delete Course
                </x-button>
            @else
                <x-button spinner positive icon="refresh" wire:click="restore">
                    Restore Assessments
                </x-button>
                <x-button negative icon="trash" wire:click="openDeleteAssessmentsModal">
                    Delete Missing Assessments
                </x-button>
            @endif
        </div>
    </div>
    <x-modal wire:model.defer="deleteMasterModalOpen">
        <x-card title="Delete Seed Course">
            <div class='rounded-lg border border-negative-600 bg-negative-50 p-4'>
                <div class="flex items-center border-b-2 border-negative-200 pb-3">
                    <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
                    <span class="ml-1 flex text-lg text-negative-600">
                        You are about to delete&nbsp;<b>{{ $master->title }}</b>
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
                    <x-button negative spinner label="Confirm" wire:click="deleteMaster" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>

    <x-modal wire:model.defer="deleteAssessmentsModalOpen">
        <x-card title="Delete Course Assessments">
            <div class='rounded-lg border border-negative-600 bg-negative-50 p-4 text-negative-600'>
                <div class="flex items-center border-b-2 border-negative-200 pb-3">
                    <x-icon name="exclamation" class="h-6 w-6 text-negative-600" />
                    <span class="ml-1 flex text-lg">
                        You are about to delete the following assessments
                        of&nbsp;<b>{{ $master->title }}</b>:
                    </span>
                </div>
                <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                    <ul class="list-disc space-y-1 text-negative-600">
                        @foreach ($missingAssessmentSeeds as $assessment)
                            <li><b>{{ $assessment->title }}</b></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-between">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button negative spinner label="Confirm" wire:click="deleteAssessments" />
                </div>
            </x-slot>
        </x-card>
    </x-modal>
</div>
