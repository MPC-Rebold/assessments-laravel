<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Course;
use App\Models\User;
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

test('SyncService updateConnectedCourses connects Courses for non-existent canvas course', function () {
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

test('SyncService updateConnectedCourses connects Courses for existing canvas course and updates max points', function () {
    $reset = CanvasService::editAssignment([config('canvas.testing_course_id'), config('canvas.testing_assessment_id')], ['points_possible' => 0.0, 'grading_type' => 'points']);
    expect($reset->status())->toBe(200);

    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@question@@answer@@');

    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));
    $enrolledUser = User::factory()->create();

    $testCourse->update(['valid_students' => [$enrolledUser->email]]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();

    expect($master->courses->count())->toBe(1)
        ->and($master->courses->first()->title)->toBe(config('canvas.testing_course_name'))
        ->and($testCourse->master->title)->toBe('__NewMaster')

        // Connects to Master's Assessments
        ->and($testCourse->assessments->count())->toBe(1)
        ->and($testCourse->assessments->first()->title)->toBe(config('canvas.testing_assessment_name'))
        ->and($testCourse->assessments->first()->pivot->assessment_canvas_id)->toBeGreaterThan(0)
        ->and($testCourse->id)->toBe(config('canvas.testing_course_id'))

        // Connects valid users
        ->and($testCourse->users->count())->toBe(1)
        ->and($testCourse->users->first()->id)->toBe($enrolledUser->id);

    $canvasAssessment = CanvasService::getAssignment($testCourse->id, $testCourse->assessments->first()->pivot->assessment_canvas_id);

    expect($canvasAssessment->json()['points_possible'])->toBe(2.0);

    SeedService::deleteMaster($master);
});

test('SyncService updateConnectedCourses disconnects Courses', function () {
    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@question@@answer@@');

    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $master->refresh();
    expect($master->courses->count())->toBe(1);

    SyncService::updateConnectedCourses($master, collect());

    $master->refresh();
    $testCourse->refresh();

    expect($master->courses->count())->toBe(0)
        ->and($testCourse->master_id)->toBeNull()
        ->and($testCourse->assessments->count())->toBe(1);

    SeedService::deleteMaster($master);
});

test('Removing course from Canvas', function () {
    SyncService::syncCourses();

});
