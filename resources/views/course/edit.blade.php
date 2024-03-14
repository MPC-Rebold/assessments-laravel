<?php

use App\Models\Master;
use App\Models\Course;

$master = Master::find(request()->route('masterId'));
$course = Course::find(request()->route('courseId'));

if (!$master || !$course) {
    abort(404);
}
?>

@section('title', 'Edit Course')

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => $master->title, 'href' => route('master.edit', $master->id)],
        ['title' => $course->title, 'href' => route('course.edit', [$master->id, $course->id])],
    ]" />
    <x-slot:content>
        <livewire:layout.section-header :header="$course->title . ' (' . $master->title . ')'" />
        <livewire:admin.specification-setting :course="$course" />
        <livewire:course.assessments-stats :course="$course" />
        <livewire:user.all-users :course="$course" />
    </x-slot:content>
</x-app-layout>
