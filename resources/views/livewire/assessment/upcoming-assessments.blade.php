<?php

use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Support\Collection;

new class extends Component {
    public int $courseId;
    public Collection $assessments;

    public function mount(): void
    {
        if (isset($this->courseId)) {
            $this->assessments = auth()
                ->user()
                ->assessments($this->courseId);
        } else {
            $this->assessments = auth()->user()->assessments();
        }

        $this->assessments = $this->assessments->take(4);
    }
}; ?>

<livewire:assessment.assessment-cards :assessments="$assessments" />
