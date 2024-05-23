<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\AssessmentCourse;
use App\Models\Master;

new class extends Component {
    public Collection $assessmentCourses;

    public function mount(Collection $assessmentCourses): void
    {
        $this->assessmentCourses = $assessmentCourses->where('assessment_canvas_id', '!=', -1)->sortBy('due_at');
    }
}; ?>

<div class="space-y-4">
    @if ($assessmentCourses->isNotEmpty())
        @foreach ($assessmentCourses as $assessmentCourse)
            @if ($assessmentCourse->assessment_canvas_id != -1)
                @if ($assessmentCourse->is_active)
                    <livewire:assessment.assessment-card-active :assessmentCourse="$assessmentCourse" :key="$assessmentCourse->id" />
                @else
                    <livewire:assessment.assessment-card-inactive :assessmentCourse="$assessmentCourse" :key="$assessmentCourse->id" />
                @endif
            @endif
        @endforeach
    @else
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="flex max-h-20 w-full items-center justify-between px-6 py-4 text-gray-900">
                No Assessments
            </div>
        </div>
    @endif
</div>
