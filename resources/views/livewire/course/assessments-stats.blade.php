<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Illuminate\Support\Collection;
use Carbon\Carbon;

new class extends Component {
    public Course $course;

    public Collection $assessmentCourses;

    public function mount(): void
    {
        $this->assessmentCourses = AssessmentCourse::where('course_id', $this->course->id)->get();
    }
}; ?>

<div class="bg-slate-100 shadow sm:rounded-lg">
    <div class="bg-white p-4 text-lg font-bold shadow sm:rounded-lg sm:px-6 sm:py-5">
        Assessments
    </div>
    <div class="space-y-4 p-4 sm:px-6">
        @if ($course->assessments->isEmpty())
            <div class="text-gray-500">
                No assessments found
            </div>
        @endif
        @foreach ($assessmentCourses as $assessmentCourse)
            <livewire:course.assessment-status :assessmentCourse="$assessmentCourse" wire:key="{{now()->toDateTimeString()}}" />
            @if (!$loop->last)
                <hr>
            @endif
        @endforeach
    </div>
</div>
