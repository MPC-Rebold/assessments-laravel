<?php

use App\Models\Assessment;
use App\Models\AssessmentCourse;

$assessment_canvas_id = request()->route('assessmentId');
$assessmentCourse = AssessmentCourse::firstWhere('assessment_canvas_id', $assessment_canvas_id);

if (!$assessmentCourse) {
    abort(404);
}

$assessment = $assessmentCourse->assessment;
$course = $assessmentCourse->course;
$questions = $assessment->questions;

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

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <livewire:layout.section-header :header="$assessment->title" />
            <livewire:assessment.instructions :assessment="$assessment" />
            @foreach ($questions as $question)
                <livewire:assessment.question :question="$question" :course="$course" :key="$question->id" />
            @endforeach
        </div>
    </div>
</x-app-layout>
