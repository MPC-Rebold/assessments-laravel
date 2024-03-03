<?php

use App\Models\User;
use App\Models\AssessmentCourse;

$user = User::find(request()->route('userId'));
$assessment = AssessmentCourse::find(request()->route('assessmentId'));
$questions = $assessment->assessment->questions;

?>

@section('title', 'Grades - ' . $user->name)

<x-app-layout>
    <livewire:layout.header :routes="[
        ['title' => 'Admin', 'href' => route('admin')],
        ['title' => 'Users', 'href' => route('user.index')],
        ['title' => $user->name, 'href' => route('user.show', $user->id)],
        ['title' => $assessment->assessment->title, 'href' => route('user.grade.show', [$user->id, $assessment->id])],
    ]" />
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <livewire:layout.section-header :header="$user->name . ' - ' . $assessment->assessment->title . ' Grades'" />
            @foreach ($questions as $question)
                <livewire:user.asessment-question-grade :user="$user" :question="$question" :assessment="$assessment"
                    wire:key="{{ $question->id }}">
            @endforeach
        </div>
    </div>
</x-app-layout>
