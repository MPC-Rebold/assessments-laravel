<?php

use App\Models\Assessment;

$assessment = Assessment::find(last(request()->segments()));
$course = $assessment->course;
?>

@section('title', $assessment->title . ' - ' . $course->title)


<x-app-layout>
    @livewire('layout.header', ['routes' => [
        ['title' => 'Courses', 'href' => route('courses')],
        ['title' => $course->title, 'href' => route('course', $course->id)],
        ['title' => $assessment->title, 'href' => route('assessment', [$course->id, $assessment->id])],
    ]])

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                ...
            </div>
        </div>
    </div>
</x-app-layout>
