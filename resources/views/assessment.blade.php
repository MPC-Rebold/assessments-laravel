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

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form wire:submit="submitGrade">
                    <?php
                    $questionNum = 0;
                    
                    foreach ($questions as $question) {
                        if ($assessment->id == $question->assessment_id) {
                            echo $question->question;
                            $questionNum++;
                            echo "</br>    
                                            <div class='textarea' wire:model='Question'+$questionNum contenteditable>
                    
                                             </div> </br>";
                        }
                    }
                    ?>
                    <div class="flex space-x-4">

                        <x-button type="submit" href='/courses'>
                            Go
                        </x-button>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>
</x-app-layout>
