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
        @foreach ($course->assessments as $assessment)
            @php($assessmentCourse = $assessmentCourses->firstWhere('assessment_id', $assessment->id))
            <div class="flex items-center justify-between">
                <div class="flex space-x-4">
                    <div>
                        {{ $assessment->title }}
                    </div>
                    <div class="text-gray-500">
                        Due at: {{ Carbon::parse($assessmentCourse->due_at)->tz('PST')->format('M j g:i A ') }}
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="hidden md:block">
                        Avg Score:
                        {{ round($assessmentCourse->getAverageGrade() * 100, 1) }}%
                    </div>
                    <div class="hidden h-2.5 w-40 rounded-full bg-white shadow dark:bg-gray-700 md:block">
                        <div class="h-2.5 rounded-full bg-positive-500 transition-all ease-out"
                            style="width: {{ round($assessmentCourses->firstWhere('assessment_id', $assessment->id)->getAverageGrade() * 100, 1) }}%">
                        </div>
                    </div>
                </div>
            </div>
            @if (!$loop->last)
                <hr>
            @endif
        @endforeach
    </div>
</div>
