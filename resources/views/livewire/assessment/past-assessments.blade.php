<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Carbon\Carbon;

new class extends Component {
    public int $courseId;

    public Collection $assessmentCourses;

    public function placeholder(): string
    {
        return <<<'HTML'
        <x-placeholder-assessment />
        HTML;
    }

    public function mount(): void
    {
        $this->assessmentCourses = auth()
            ->user()
            ->courses->find($this->courseId)
            ->assessmentCourses->filter(fn($assessmentCourse) => $assessmentCourse->isPastDue())
            ->sortBy('due_at');
    }
}; ?>

<livewire:assessment.assessment-cards :assessmentCourses="$assessmentCourses" />
