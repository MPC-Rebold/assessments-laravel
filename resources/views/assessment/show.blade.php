<?php

use App\Models\Assessment;
use App\Models\AssessmentCourse;
use Carbon\Carbon;

$assessment_canvas_id = request()->route('assessmentId');
$assessmentCourse = AssessmentCourse::firstWhere('assessment_canvas_id', $assessment_canvas_id);

if (!$assessmentCourse) {
    abort(404);
}

$assessment = $assessmentCourse->assessment;
$course = $assessmentCourse->course;
$questions = $assessment->questions->sortBy('number');

if ($assessmentCourse->due_at) {
    $dueAt = Carbon::parse($assessmentCourse->due_at)
        ->setTimezone('PST')
        ->format('M j, g:i A T');
} else {
    $dueAt = null;
}
?>

@section('title', $assessment->title . ' - ' . $course->title)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Courses', 'href' => route('course.index')],
        ['title' => $course->title, 'href' => route('course.show', $course->id)],
        [
            'title' => $assessment->title,
            'href' => route('assessment.show', [$course->id, $assessmentCourse->assessment_canvas_id]),
        ],
    ]" />

    <div class="mx-auto max-w-7xl space-y-6 pb-20 pt-10 sm:px-6 lg:px-8">
        <div class="sm: space-y-4 px-2">
            <div class="flex flex-wrap items-baseline justify-between gap-2 text-nowrap">
                <h1 class="text-2xl">{{ $assessment->title }}</h1>
                <div class="flex items-baseline text-slate-800">
                    Due at: {{ $dueAt ?? 'N/A' }}
                </div>
            </div>
            <hr class="border-2">
        </div>

        <livewire:assessment.instructions />
        @foreach ($questions as $question)
            <livewire:assessment.question :question="$question" :course="$course" :key="$question->id" />
        @endforeach

        <div class="mt-1 flex items-center justify-between px-2 sm:px-0">
            <div>
                <x-canvas-button class="h-10 w-fit" :href="'/courses/' . $course->id . '/assignments/' . $assessmentCourse->assessment_canvas_id">
                    <div class="ms-2 text-nowrap text-base font-extrabold">
                        Canvas
                    </div>
                </x-canvas-button>
            </div>
            <div>
                <x-button positive>
                    <p class="text-base">
                        Submit to Canvas
                    </p>
                </x-button>
            </div>
        </div>
    </div>

    <livewire:assessment.progress-footer :assessment="$assessment" :course="$course" />

</x-app-layout>
