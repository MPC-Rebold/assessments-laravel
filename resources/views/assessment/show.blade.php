<?php

use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

$assessment = Assessment::find(last(request()->segments()));
$course = $assessment->course;
$questions = DB::table('questions')->get();

?>

@section('title', $assessment->title . ' - ' . $course->title)

<x-app-layout>
    @livewire('layout.header', ['routes' => [['title' => 'Courses', 'href' => route('courses')], ['title' => $course->title, 'href' => route('course', $course->id)], ['title' => $assessment->title, 'href' => route('assessment', [$course->id, $assessment->id])]]])

    ASSESSMENT
</x-app-layout>
