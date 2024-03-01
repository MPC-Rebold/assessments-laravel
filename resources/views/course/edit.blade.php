<?php

use App\Models\Master;
use App\Models\Course;

$master = Master::find(request()->route('masterId'));
$course = Course::find(request()->route('courseId'));
?>

@section('title', 'Edit Course')

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => $master->title, 'href' => route('master.edit', $master->id)],
        ['title' => $course->title, 'href' => route('course.edit', [$master->id, $course->id])],
    ]" />
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:layout.section-header :header="$course->title . ' (' . $master->title . ')'" />
            <livewire:course.students :course="$course" />
        </div>
    </div>
</x-app-layout>
