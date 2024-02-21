<?php

namespace App\Livewire\Admin;

use App\Models\Settings;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class SpecificationSetting extends Component
{
    use Actions;

    public bool $specification_grading;

    public string $specification_grading_threshold;

    public function with(): void
    {
        $this->specification_grading = Settings::sole()->specification_grading;

        if ($this->specification_grading) {
            $this->specification_grading_threshold = Settings::sole()->specification_grading_threshold * 100 . '%';
        } else {
            $this->specification_grading_threshold = 'OFF';
        }
    }

    public function updateSpecificationGrading(): void
    {
        $specification_grading = $this->specification_grading_threshold !== 'OFF';

        if ($specification_grading) {
            $specification_grading_threshold = (int) $this->specification_grading_threshold / 100;
        } else {
            $specification_grading_threshold = -1;
        }

        Settings::sole()->update([
            'specification_grading' => $specification_grading,
            'specification_grading_threshold' => $specification_grading_threshold,
        ]);

        $this->specification_grading = Settings::sole()->specification_grading;

        $this->notification()->success(
            'Specification Grading Turned ' . ($specification_grading ? 'On' : 'Off'),
        );
    }

    public function render(): View
    {
        return view('livewire.admin.specification-setting');
    }
}
