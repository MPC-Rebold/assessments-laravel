<?php

use App\Models\Master;
use Livewire\Volt\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\Attributes\Validate;
use WireUi\Traits\Actions;
use App\Services\SyncService;
use App\Services\SeedService;
use Livewire\WithFileUploads;

new class extends Component {
    use Actions;
    use WithFileUploads;

    #[Validate(['uploadedAssessments' => 'required', 'uploadedAssessments.*' => 'file|mimes:txt'])]
    public array $uploadedAssessments;

    public Master $master;

    public function mount(Master $master): void
    {
        $this->master = $master;
    }

    public function saveUploadedAssessments(): Redirector|null
    {
        $this->validate();

        try {
            $uploadedAssessments = SeedService::uploadAssessments($this->master, $this->uploadedAssessments);
            SyncService::syncUpdatedAssessments($uploadedAssessments);
        } catch (Exception $e) {
            $this->notification()->error('Failed to upload Assessments', $e->getMessage());
            return null;
        }

        return redirect(request()->header('Referer'));
    }
}; ?>

<div class="w-full" x-data="{ open: false }">
    <div @click="open = true" :class="open ? 'hidden' : 'block'">
        <x-button icon="plus" class="w-full rounded-t-none hover:!bg-secondary-500 hover:text-white">
            Add Assessments
        </x-button>
    </div>
    <div class="overflow-hidden transition-all duration-500"
        :class="{ 'max-h-0 invisible': !open, 'max-h-[100vh] p-4 sm:px-6': open }">

        <form wire:submit="saveUploadedAssessments">
            @csrf

            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <input type="file" wire:model.defer="uploadedAssessments" name="uploaded_assessments[]" multiple
                        accept=".txt">
                    @error('uploadedAssessments.*')
                        <div class="text-negative-500">
                            {{ $message }}</div>
                    @enderror
                    @error('uploadedAssessments')
                        <div class="text-negative-500">
                            {{ $message }}</div>
                    @enderror
                </div>
                <x-button positive type="submit" class="min-w-20">
                    Upload
                </x-button>
            </div>
        </form>
    </div>
</div>
