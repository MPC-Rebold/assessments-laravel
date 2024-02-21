<?php

use Livewire\Volt\Component;
use App\Models\Course;

new class extends Component {
    public int $courseId;
    public array $assessments;

    public function mount(): void
    {
        if (isset($this->courseId)) {
            $this->assessments = array_slice(auth()->user()->assessments($this->courseId), 0, 4);
        } else {
            $this->assessments = array_slice(auth()->user()->assessments(), 0, 4);
        }
    }
}; ?>

<livewire:assessment.assessment-cards :assessments="$assessments"/>

