<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Master;
use App\Models\Settings;

class SettingsController extends Controller
{
    public function __invoke()
    {
        $settings = Settings::first();
        if (! $settings) {
            abort(404);
        }

        $masters = Master::all();
        $courses = Course::all();
        $assessments = Assessment::all();
        $assessmentCourses = AssessmentCourse::all();

        $res = [
            'settings' => $settings->toArray(),
            'masters' => $masters->toArray(),
            'courses' => $courses->toArray(),
            'assessments' => $assessments->toArray(),
            'assessmentCourses' => $assessmentCourses->toArray(),
        ];

        return response()->json($res);
    }
}
