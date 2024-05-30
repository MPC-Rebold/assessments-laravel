<?php

use App\Models\Master;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

$master = Master::find(request()->route('masterId'));
$course = Course::find(request()->route('courseId'));

if (!$master || !$course) {
    abort(404);
}

$missingAssessments = $master->status->missing_assessments->where('pivot.course_id', $course->id);
?>

@section('title', 'Edit Course')

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        [
            'title' => $master->title,
            'href' => route('master.edit', $master->id),
        ],
        [
            'title' => $course->title,
            'href' => route('course.edit', [$master->id, $course->id]),
        ],
    ]" />
    <x-slot:content>
        @if ($missingAssessments->isNotEmpty())
            <livewire:master.status-warning :missingCourses="new Collection()" :missingAssessments="$missingAssessments" />
        @endif
        <livewire:layout.section-header :header="$course->title . ' (' . $master->title . ')'" />
        <livewire:admin.specification-setting :course="$course" />
        <livewire:course.list-assessment-courses :course="$course" />
        <livewire:user.list-users :course="$course" />
    </x-slot:content>
</x-app-layout>
