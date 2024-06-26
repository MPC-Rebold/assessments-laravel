<?php

use App\Models\User;
use App\Models\AssessmentCourse;

$user = User::find(request()->route('userId'));
$assessmentCourse = AssessmentCourse::find(request()->route('assessmentId'));
$questions = $assessmentCourse->assessment->questions;

?>

@section('title', 'Grades - ' . $user->name)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => 'Users', 'href' => route('user.index')],
        ['title' => $user->name, 'href' => route('user.show', $user->id)],
        [
            'title' => $assessmentCourse->course->title . ' - ' . $assessmentCourse->assessment->title,
            'href' => route('user.grade.show', [$user->id, $assessmentCourse->id]),
        ],
    ]" />
    <x-slot:content>
        <livewire:layout.section-header :header="$user->name .
            ' - ' .
            $assessmentCourse->course->title .
            ' - ' .
            $assessmentCourse->assessment->title .
            ' Grades'" />

        <livewire:user.assessment-grade :user="$user" :assessmentCourse="$assessmentCourse" />

        @foreach ($questions as $question)
            <livewire:user.assessment-question-grade :user="$user" :question="$question" :assessment="$assessmentCourse"
                wire:key="{{ $question->id }}">
        @endforeach
    </x-slot:content>
</x-app-layout>
