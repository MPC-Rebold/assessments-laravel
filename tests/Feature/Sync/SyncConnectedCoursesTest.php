<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Course;
use App\Services\CanvasService;
use App\Services\SeedService;
use App\Services\SyncService;
use Tests\SeedProtection;

uses()->group('sync');

beforeAll(function () {
    SeedProtection::backupSeed();
});

beforeEach(function () {
    SeedProtection::preTest();
});

afterEach(function () {
    SeedProtection::postTest();
});

afterAll(function () {
    SeedProtection::restoreSeed();
});


test('TESTING_CANVAS_COURSE to exist on canvas', function () {
    $availableCourses = collect(CanvasService::getCourses());
    $availableCourseIds = $availableCourses->pluck('id')->toArray();
    $availableAssessments = collect(CanvasService::getCourseAssignments(config('canvas.testing_course_id'))->json());

    expect($availableCourseIds)->toContain(config('canvas.testing_course_id'))
        ->and($availableCourses->firstWhere('id', config('canvas.testing_course_id'))['name'])->toBe(config('canvas.testing_course_name'))
        ->and($availableAssessments->pluck('name'))->toContain(config('canvas.testing_assessment_name'))
        ->and($availableAssessments->pluck('name'))->not()->toContain('__NewAssessment');
});

test('SyncService updateConnectedCourses connects Courses', function () {
    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', '__NewAssessment', 'question@@answer@@');

    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();

    expect($master->courses->count())->toBe(1)
        ->and($master->courses->first()->title)->toBe(config('canvas.testing_course_name'))
        ->and($testCourse->master->title)->toBe('__NewMaster')
        ->and($testCourse->assessments->count())->toBe(1)
        ->and($testCourse->assessments->first()->title)->toBe('__NewAssessment')
        ->and($testCourse->assessments->first()->pivot->assessment_canvas_id)->toBe(-1);

    SeedService::deleteMaster($master);
});

