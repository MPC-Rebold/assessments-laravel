<?php

use App\Models\Course;

$course = Course::find(last(request()->segments()));
?>

@section('title', $course->title)


<x-app-layout>
    @livewire('layout.header', ['routes' => [
        ['title' => 'Courses', 'href' => route('courses')],
        ['title' => $course->title, 'href' => route('course', $course->id)],
    ]])
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @livewire('layout.section-header', ['header' => __('Current Assessments')])
            @livewire('assessment.upcoming-assessments', ['courseId' => $course->id])
        </div>
    </div>
</x-app-layout>
