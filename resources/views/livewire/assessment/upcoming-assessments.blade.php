<?php

use Livewire\Volt\Component;
use App\Models\Course;

new class extends Component {
    public int $courseId;

    public function with(): array
    {
        if (isset($this->courseId)) {
            return [
                'assessments' => auth()->user()->assessments($this->courseId)
            ];
        }

        return [
            'assessments' => array_slice(auth()->user()->assessments(), 0, 4),
        ];
    }
}; ?>

<livewire:assessment.assessment-cards :assessments="$assessments"/>

