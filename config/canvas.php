<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Canvas API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for your Canvas account.
    |
    */
    'token' => env('CANVAS_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Canvas API Host
    |--------------------------------------------------------------------------
    |
    | This value is the host for the Canvas API.
    |
    */
    'host' => env('CANVAS_API_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Canvas API Testing Course ID
    |--------------------------------------------------------------------------
    |
    | This value is the course name for testing
    |
    */
    'testing_course_id' => (int) env('TESTING_CANVAS_COURSE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Canvas API Testing Course ID
    |--------------------------------------------------------------------------
    |
    | This value is the course name for testing
    |
    */
    'testing_course_name' => env('TESTING_CANVAS_COURSE_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Canvas API Testing Assessment Name
    |--------------------------------------------------------------------------
    |
    | This value is the assessment name for testing
    |
    */
    'testing_assessment_id' => (int) env('TESTING_CANVAS_ASSESSMENT_ID', '__TestAssessment'),

    /*
    |--------------------------------------------------------------------------
    | Canvas API Testing Assessment Name
    |--------------------------------------------------------------------------
    |
    | This value is the assessment name for testing
    |
    */
    'testing_assessment_name' => env('TESTING_CANVAS_ASSESSMENT_NAME', '__TestAssessment'),

];
