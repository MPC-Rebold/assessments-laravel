<?php

use Livewire\Volt\Component;
use App\Models\Course;

new class extends Component {
    public int $courseId;
    public array $assessments;

    public function mount(): void
    {
        if (isset($this->courseId)) {
            $this->assessments = auth()
                ->user()
                ->assessments($this->courseId);
        } else {
            $this->assessments = auth()->user()->assessments();
        }
        $this->assessments = array_slice($this->assessments, 0, 4);
    }
}; ?>

<livewire:assessment.assessment-cards :assessments="$assessments" />
