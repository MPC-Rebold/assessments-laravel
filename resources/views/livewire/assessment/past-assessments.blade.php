<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Carbon\Carbon;

new class extends Component {
    public int $courseId;

    public Collection $assessments;

    public function mount(): void
    {
        $assessments = auth()
            ->user()
            ->courses->find($this->courseId)->assessments;
        $this->assessments = $assessments->filter(fn($assessment) => $assessment->pivot->due_at !== null && Carbon::parse($assessment->pivot->due_at)->isPast())->sortBy('pivot.due_at');

        $this->assessments = $this->assessments->take(4);
    }
}; ?>

<livewire:assessment.assessment-cards :assessments="$assessments" />
