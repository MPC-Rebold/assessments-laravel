<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\AssessmentCourse;
use Illuminate\Support\Collection;
use Carbon\Carbon;

new class extends Component {
    public Course $course;

    public Collection $assessmentCourses;

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->assessmentCourses = $course->assessmentCourses->sort(function (AssessmentCourse $a, AssessmentCourse $b) {
            $titleA = $a->assessment->title;
            $titleB = $b->assessment->title;

            preg_match('/\d+$/', $titleA, $matchesA);
            preg_match('/\d+$/', $titleB, $matchesB);

            if (!empty($matchesA) && !empty($matchesB)) {
                return (int)$matchesA[0] <=> (int)$matchesB[0];
            }

            return $titleA <=> $titleB;
        });
    }
}; ?>

<div class="bg-slate-100 shadow sm:rounded-lg">
    <div class="bg-white p-4 text-lg font-bold shadow sm:rounded-lg sm:px-6 sm:py-5">
        Assessments
    </div>
    <div class="space-y-4 p-4 sm:px-6">
        @if ($assessmentCourses->isEmpty())
            <div class="text-gray-500">
                No assessments found
            </div>
        @endif
        @foreach ($assessmentCourses as $assessmentCourse)
            <livewire:course.assessment-course :assessmentCourse="$assessmentCourse"
                                               wire:key="{{ now()->toDateTimeString() }}" />
            @if (!$loop->last)
                <hr>
            @endif
        @endforeach
    </div>
</div>
