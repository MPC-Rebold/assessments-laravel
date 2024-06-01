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

    public bool $specification_grading;

    public string $specification_grading_threshold;

    public bool $modalOpen = false;

    public function mount(Course $course): void
    {
        $this->course = $course;

        $this->specification_grading = $this->course->specification_grading;

        if ($this->specification_grading) {
            $this->specification_grading_threshold = $this->course->specification_grading_threshold * 100 . '%';
        } else {
            $this->specification_grading_threshold = 'OFF';
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
        $specification_grading = $this->specification_grading_threshold !== 'OFF';

        if ($specification_grading) {
            $specification_grading_threshold = (int) $this->specification_grading_threshold / 100;
        } else {
            $specification_grading_threshold = -1;
        }

        try {
            SyncService::updateSpecificationGrading($this->course, $specification_grading_threshold);
        } catch (Exception $e) {
            $this->notification()->error('Failed to update Specification Grading', $e->getMessage());

            return;
        }

        $this->notification()->success('Specification Grading Turned ' . ($specification_grading ? 'On' : 'Off'));
        $this->modalOpen = false;
    }

    public function render(): View
    {
        return view('livewire.admin.specification-setting');
    }
}
