<?php

use Livewire\Volt\Component;
use App\Models\Master;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use WireUi\Traits\Actions;
use App\Livewire\Admin\Sync;
use App\Models\Assessment;
use App\Services\CanvasService;

new class extends Component {
    use WithFileUploads;
    use Actions;

    public Master $master;
    public Collection $assessments;

    public array $conflictingNames = [];
    public bool $showUpload = false;
    public bool $forceModalOpen = false;

    #[Validate(['uploadedAssessments' => 'required', 'uploadedAssessments.*' => 'file|mimes:txt'])]
    public array $uploadedAssessments = [];

    public function mount(Master $master): void
    {
        $this->master = $master;
        $this->assessments = $master->assessments->sortBy('title');
    }

    public function closeModal(): void
    {
        $this->forceModalOpen = false;
    }

    public function save(bool $force = false): void
    {
        $this->validate();

        $existingNames = $this->assessments->pluck('title')->toArray();
        $uploadedNames = array_map(fn($assessment) => pathinfo($assessment->getClientOriginalName(), PATHINFO_FILENAME), $this->uploadedAssessments);

        $this->conflictingNames = array_intersect($existingNames, $uploadedNames);

        DB::beginTransaction();
        if ($force) {
            Assessment::where('master_id', $this->master->id)
                ->whereIn('title', $this->conflictingNames)
                ->delete();
        } else if (!empty($this->conflictingNames)) {
            $this->forceModalOpen = true;
            DB::rollBack();
            return;
        }

        try {
            foreach ($this->uploadedAssessments as $uploadedAssessment) {
                $fileName = $uploadedAssessment->getClientOriginalName();
                $master = $this->master->title;

                $uploadedAssessment->storeAs("uploads/$master", $fileName);
                rename(storage_path("app/uploads/$master/$fileName"), database_path("seed/$master/$fileName"));
            }
        } catch (Exception $e) {
            $this->notification()->error("Failed to upload assessment", $e->getMessage());
            return;
            DB::rollBack();
        }
        DB::commit();

        try {
            $sync = new Sync();
            $sync->sync();

            if ($force) {
                $assessments = $this->master->courses->flatMap->assessments->whereIn('title', $this->conflictingNames);
                $assessmentCourses = $assessments->flatMap->assessmentCourses->unique('id');
                $canvasService = new CanvasService();
                $canvasService->regradeAssessmentCourses($assessmentCourses);
            }
        } catch (Exception $e) {
            $this->notification()->error("Failed to sync new assessments", $e->getMessage());
            return;
        }

        $this->dispatch('refresh');
        $this->mount($this->master);
        $this->notification()->success("Assessment uploaded successfully", implode($uploadedNames) . " uploaded to $master");
        $this->showUpload = false;
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
                                    Questions: {{ $assessment->questions->count() }}
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
        <div class="p-4 sm:px-6 w-full" x-data="{open: @entangle('showUpload')}">
            <div @click="open = true" :class="open ? 'hidden' : 'block'">
                <x-button icon="plus" class="w-full">
                    Add Assessments
                </x-button>
            </div>
            <div class="overflow-hidden transition-all duration-500"
                 :class="{ 'max-h-0 invisible': !open, 'max-h-[100vh]': open }">

                {{--                <form action="{{ route('assessment.upload') }}" method="POST" enctype="multipart/form-data">--}}
                <form wire:submit="save">
                    @csrf

                    <div class="flex items-center justify-between">
                        <div
                            x-data="{ uploading: false, progress: 0 }"
                            x-on:livewire-upload-start="uploading = true"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-cancel="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress;"
                        >
                            <!-- File Input -->
                            <div class="space-y-1 ">
                                <input type="file" wire:model="uploadedAssessments" name="uploaded_assessments[]"
                                       multiple>
                                @error('uploadedAssessments.*')
                                <div class="text-negative-500">{{ $message }}</div> @enderror
                                @error('uploadedAssessments')
                                <div class="text-negative-500">{{ $message }}</div> @enderror
                            </div>
                            <!-- Progress Bar -->
                            <div x-show="uploading">
                                <progress max="100" x-bind:value="progress"></progress>
                            </div>

                        </div>
                        <x-button positive type="submit" class="min-w-20">Submit</x-button>
                    </div>
                </form>
                <x-modal wire:model.defer="forceModalOpen">
                    <x-card title="Conflicting Assessment Names">
                        <div class='rounded-lg border border-warning-600 bg-warning-50 p-4'>
                            <div class="flex items-center border-b-2 border-warning-200 pb-3">
                                <x-icon name="exclamation" class="h-6 w-6 text-warning-700" />
                                <div class="ml-1 text-lg text-warning-700">
                                    The following assessments already exist on <b>{{$master->title}}</b>
                                </div>
                            </div>
                            <div class="ml-5 mt-2 flex items-center justify-between pl-1">
                                <ul class="list-disc space-y-1 text-warning-700">
                                    @foreach ($conflictingNames as $conflictingName)
                                        <li>{{$conflictingName}}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="text-warning-700 mt-2">
                                Do you want to replace them? This will delete the existing assessments and any associated grades.
                            </div>
                        </div>
                        <x-slot name="footer">
                            <div class="flex justify-between">
                                <x-button flat label="Cancel" wire:click="closeModal" />
                                <x-button warning label="Replace" wire:click="save(true)" />
                            </div>
                        </x-slot>
                    </x-card>
                </x-modal>
            </div>
        </div>
    </div>
</div>
