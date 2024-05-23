<?php

use Livewire\Volt\Component;
use App\Services\SeedService;
use App\Models\Assessment;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

new class extends Component {
    use Actions;

    public Assessment $assessment;

    public function downloadAssessment(): BinaryFileResponse|null
    {
        $assessmentPath = SeedService::getAssessmentPath($this->assessment);

        if (!file_exists($assessmentPath)) {
            $this->notification()->error('Error downloading assessment', 'Assessment file not found.');
            return null;
        }

        return response()->download($assessmentPath);
    }

    public function mount(Assessment $assessment): void
    {
        $this->assessment = $assessment;
    }
}; ?>

<div class="bg-white p-4 shadow sm:rounded-lg sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-lg font-bold">
            Download Raw Assessment
        </div>
        <x-button sky class="w-28" wire:click="downloadAssessment">
            <div class="flex items-center space-x-2">
                <x-icon solid name="download" class="h-4 w-4" />
                <div>Download</div>
            </div>
        </x-button>
    </div>
</div>
