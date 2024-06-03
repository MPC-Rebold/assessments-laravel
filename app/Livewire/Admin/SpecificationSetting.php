<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Services\SyncService;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class SpecificationSetting extends Component
{
    use Actions;

    public Course $course;

    public bool $specificationGrading;

    public string $specificationGradingThreshold;

    public bool $modalOpen = false;

    public function mount(Course $course): void
    {
        $this->course = $course;

        $this->specificationGrading = $this->course->specification_grading;

        if ($this->specificationGrading) {
            $this->specificationGradingThreshold = $this->course->specification_grading_threshold * 100 . '%';
        } else {
            $this->specificationGradingThreshold = 'OFF';
        }
    }

    public function openModal(): void
    {
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->mount($this->course);
        $this->render();
    }

    public function updateSpecificationGrading(): void
    {
        $specificationGrading = $this->specificationGradingThreshold !== 'OFF';

        if ($specificationGrading) {
            $specificationGradingThreshold = (int) $this->specificationGradingThreshold / 100;
        } else {
            $specificationGradingThreshold = -1;
        }

        try {
            SyncService::updateSpecificationGrading($this->course, $specificationGradingThreshold);
        } catch (Exception $e) {
            $this->notification()->error('Failed to update Specification Grading', $e->getMessage());

            return;
        }

        $this->notification()->success('Specification Grading Turned ' . ($specificationGrading ? 'On' : 'Off'));
        $this->modalOpen = false;
    }

    public function render(): View
    {
        return view('livewire.admin.specification-setting');
    }
}
