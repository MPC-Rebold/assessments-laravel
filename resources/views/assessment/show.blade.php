<?php

use Livewire\Volt\Component;
use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use Illuminate\Support\Collection;
use Carbon\Carbon;

$assessment_canvas_id = request()->route('assessmentId');
$assessmentCourse = AssessmentCourse::firstWhere('assessment_canvas_id', $assessment_canvas_id);

if (!$assessmentCourse) {
    abort(404);
}
?>

<x-app-layout>
    <livewire:assessment.assessment-body :assessmentCourse="$assessmentCourse" />
</x-app-layout>
