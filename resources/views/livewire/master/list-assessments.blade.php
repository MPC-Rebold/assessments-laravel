<?php

use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use App\Models\Master;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use WireUi\Traits\Actions;
use App\Livewire\Admin\Sync;
use App\Models\Assessment;
use App\Services\CanvasService;
use App\Services\SyncService;
use Livewire\Features\SupportRedirects\Redirector;

new class extends Component {
    use WithFileUploads;
    use Actions;

    public Master $master;
    public Collection $assessments;

    public bool $showUpload = false;

    #[Validate(['uploadedAssessments' => 'required', 'uploadedAssessments.*' => 'file|mimes:txt'])]
    public array $uploadedAssessments;

    public function mount(Master $master): void
    {
        $this->master = $master;
        $this->assessments = $master->assessments->sortBy('title');
        $this->uploadedAssessments = [];
    }

    public function saveAddedAssessments(): Redirector|null
    {
        $this->validate();

        $existingNames = $this->assessments->pluck('title')->toArray();
        $uploadedNames = array_map(fn($assessment) => trim(pathinfo($assessment->getClientOriginalName(), PATHINFO_FILENAME)), $this->uploadedAssessments);

        if (in_array('', $uploadedNames)) {
            $this->notification()->error('Failed to upload assessment', 'One or more files have no name');
            return null;
        }

        if (!empty(array_intersect($existingNames, $uploadedNames))) {
            $conflictingNames = array_intersect($existingNames, $uploadedNames);

            $this->notification()->error('Failed to upload assessment', 'The assessments: ' . implode(', ', $conflictingNames) . ' have conflicting names');
            return null;
        }

        if (count($uploadedNames) !== count(array_unique($uploadedNames))) {
            $duplicateNames = array_diff_assoc($uploadedNames, array_unique($uploadedNames));

            $this->notification()->error('Failed to upload assessment', 'The assessments: ' . implode(', ', $duplicateNames) . ' have duplicate names');
            return null;
        }

        try {
            foreach ($this->uploadedAssessments as $uploadedAssessment) {
                $fileName = $uploadedAssessment->getClientOriginalName();
                $master = $this->master->title;

                $uploadedAssessment->storeAs("tmp/$master", $fileName);
                rename(storage_path("app/tmp/$master/$fileName"), database_path("seed/$master/$fileName"));
                rmdir(storage_path("app/tmp/$master"));
            }
        } catch (Exception $e) {
            $this->notification()->error('Failed to upload assessment', $e->getMessage());
            return null;
        }

        try {
            SyncService::sync();
        } catch (Exception $e) {
            $this->notification()->error('Failed to sync new assessments', $e->getMessage());
            return null;
        }

        return redirect(request()->header('Referer'));
    }
}; ?>

<div class="bg-slate-100 shadow sm:rounded-lg">
    <div class="flex items-center bg-white p-4 shadow sm:rounded-lg sm:px-6 sm:py-4">
        <div class="text-lg font-bold">
            Assessments
        </div>
    </div>
    <div>
        @if ($assessments->isEmpty())
            <div class="p-4 text-center sm:px-6">
                <p class="text-lg font-bold text-gray-400">
                    No Assessments
                </p>
            </div>
        @else
            @foreach ($assessments as $assessment)
                <a href="{{ route('assessment.edit', [$master->id, $assessment->id]) }}" wire:navigate>
                    <div
                        class="group flex flex-wrap items-center justify-between gap-4 rounded-lg px-4 py-3 transition-all hover:bg-gray-200 hover:shadow sm:px-6">
                        <div class="flex items-center space-x-4">
                            <div class="group-hover:underline">
                                {{ $assessment->title }}
                            </div>
                            <div class="hidden sm:flex">
                                <span class="text-gray-500">
                                    Questions:
                                    {{ $assessment->questions->count() }}
                                </span>
                            </div>
                        </div>
                        <x-button secondary icon="pencil" class="group-hover:scale-[1.05]">
                            Edit
                        </x-button>
                    </div>
                </a>
                <hr class="mx-4 sm:mx-6">
            @endforeach
        @endif
        <div class="w-full p-4 sm:px-6" x-data="{ open: @entangle('showUpload') }">
            <div @click="open = true" :class="open ? 'hidden' : 'block'">
                <x-button icon="plus" class="w-full hover:!bg-secondary-500 hover:text-white">
                    Add Assessments
                </x-button>
            </div>
            <div class="overflow-hidden transition-all duration-500"
                :class="{ 'max-h-0 invisible': !open, 'max-h-[100vh]': open }">

                <form wire:submit="saveAddedAssessments">
                    @csrf

                    <div class="flex items-center justify-between">
                        <div x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-cancel="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress;">
                            <div class="space-y-1">
                                <input type="file" wire:model.defer="uploadedAssessments"
                                    name="uploaded_assessments[]" multiple accept=".txt">
                                @error('uploadedAssessments.*')
                                    <div class="text-negative-500">
                                        {{ $message }}</div>
                                @enderror
                                @error('uploadedAssessments')
                                    <div class="text-negative-500">
                                        {{ $message }}</div>
                                @enderror
                            </div>
                            {{--                            <!-- Progress Bar --> --}}
                            {{--                            <div x-show="uploading" class="w-full"> --}}
                            {{--                                <progress max="100" x-bind:value="progress"></progress> --}}
                            {{--                            </div> --}}

                        </div>
                        <x-button positive type="submit" class="min-w-20">
                            Upload
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
