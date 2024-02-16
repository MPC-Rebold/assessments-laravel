<?php

use App\Models\Assessment;

$assessment = Assessment::find(last(request()->segments()));
$course = $assessment->course;
?>

@section('title', $assessment->title . ' - ' . $course->title)


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center space-x-2">
            <div class="select-none">
                {{ __('Courses') }}
            </div>
            <x-icon name="chevron-right" class="h-5" solid/>
            <a class="hover:text-indigo-600" href="{{route('courses', $course->id)}}" wire:navigate>
                {{ $course->title }}
            </a>
            <x-icon name="chevron-right" class="h-5" solid/>
            <a class="hover:text-indigo-600" href="{{route('assessment', [$course->id, $assessment->id])}}"
               wire:navigate>
                {{ $assessment->title }}
            </a>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                ...
            </div>
        </div>
    </div>
</x-app-layout>
