<?php

use Livewire\Volt\Component;
use App\Services\SeedService;
use App\Models\Master;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

new class extends Component {
    use Actions;

    public Master $master;

    public function downloadMaster(): BinaryFileResponse|null
    {
        if (!SeedService::isValidMaster($this->master)) {
            $this->notification()->error('Error downloading course', 'Course folder not found.');
            return null;
        }

        $assessmentTitles = SeedService::getAssessments($this->master);
        $assessmentPaths = array_map(fn($assessmentTitle) => SeedService::getAssessmentPathByTitles($this->master->title, $assessmentTitle), $assessmentTitles);

        if (!is_dir(storage_path('app/tmp'))) {
            mkdir(storage_path('app/tmp'));
        }

        $zipPath = storage_path('app/tmp/' . $this->master->title  . '.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->notification()->error('Error downloading course', 'Failed to create zip file.');
            return null;
        }

        foreach ($assessmentPaths as $assessmentPath) {
            $zip->addFile($assessmentPath, basename($assessmentPath));
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend();
    }

    public function mount(Master $master): void
    {
        $this->master = $master;
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-lg font-bold">
            Download Raw Course
        </div>
        <x-button sky class="w-28" wire:click="downloadMaster">
            <div class="flex items-center space-x-2">
                <x-icon solid name="download" class="h-4 w-4" />
                <div>Download</div>
            </div>
        </x-button>
    </div>
</div>
