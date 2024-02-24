<?php

use App\Models\Course;

$course = Course::find(request()->route('courseId'));
if (!$course) {
    abort(404);
}

?>

@section('title', $course->title)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Courses', 'href' => route('course.index')],
        ['title' => $course->title, 'href' => route('course.show', $course->id)],
    ]" />

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <livewire:layout.section-header :header="__('Current Assessments')" />
                <livewire:assessment.upcoming-assessments :courseId="$course->id" />
            </div>
            <div class="space-y-4">
                <livewire:layout.section-header :header="__('Past Assessments')" />
                <livewire:assessment.past-assessments :courseId="$course->id" />
            </div>
        </div>
    </div>
</x-app-layout>
