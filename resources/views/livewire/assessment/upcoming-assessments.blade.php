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
        if (isset($this->courseId)) {
            $this->assessmentCourses = auth()
                ->user()
                ->courses->find($this->courseId)
                ->assessmentCourses->where('assessment_canvas_id', '!=', '-1')
                ->filter(fn($assessmentCourse) => !$assessmentCourse->isPastDue())
                ->sortBy('due_at');
        } else {
            $assessmentCourses = auth()->user()->courses->flatMap->assessmentCourses;
            $this->assessmentCourses = $assessmentCourses->where('assessment_canvas_id', '!=', '-1')->filter(fn($assessmentCourse) => !$assessmentCourse->isPastDue())->flatten()->sortBy('due_at');
        }
    }
}; ?>

<livewire:assessment.assessment-cards :assessmentCourses="$assessmentCourses" />
