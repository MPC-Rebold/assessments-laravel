<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Course;
use App\Services\CanvasService;
use App\Services\SeedService;
use App\Services\SyncService;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Volt;
use Tests\SeedProtection;

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

test('master.upload-assessments creates single Assessment and connects to connected Courses and sets max points', function () {
    $reset = CanvasService::editAssignment([config('canvas.testing_course_id'), config('canvas.testing_assessment_id')], ['points_possible' => 0, 'grading_type' => 'pass_fail']);
    expect($reset->status())->toBe(200);

    $master = SeedService::createMaster('__NewMaster');
    $assessments = [TemporaryUploadedFile::fake()->create(config('canvas.testing_assessment_name') . '.txt', 'question@@answer@@')];

    SyncService::syncCourses();
    $testCourse = Course::first();

    SyncService::updateConnectedCourses($master, collect([$testCourse]));
    $testCourse->refresh();

    Volt::test('master.upload-assessments', ['master' => $master])
        ->set('uploadedAssessments', $assessments)
        ->call('saveUploadedAssessments');

    expect($testCourse->assessments->count())->toBe(1)
        ->and($testCourse->assessments->first()->pivot->assessment_canvas_id)->toBe(config('canvas.testing_assessment_id'));

    $canvasAssignment = CanvasService::getAssignment($testCourse->id, config('canvas.testing_assessment_id'))->json();
    expect((int) $canvasAssignment['points_possible'])->toBe(1)
        ->and($canvasAssignment['grading_type'])->toBe('points');

    SeedService::deleteMaster($master);
});

test('master.upload-assessments creates single Assessment on connected Course and assigns missing status', function () {
    $master = SeedService::createMaster('__NewMaster');
    $assessments = [TemporaryUploadedFile::fake()->create('__NewAssessment.txt', 'question@@answer@@')];

    SyncService::syncCourses();
    $testCourse = Course::first();

    SyncService::updateConnectedCourses($master, collect([$testCourse]));
    $testCourse->refresh();

    Volt::test('master.upload-assessments', ['master' => $master])
        ->set('uploadedAssessments', $assessments)
        ->call('saveUploadedAssessments');

    expect($testCourse->assessments->count())->toBe(1)
        ->and($testCourse->assessments->first()->pivot->assessment_canvas_id)->toBe(-1)
        ->and($master->status->missing_assessments->where('pivot.course_id', $testCourse->id)->count())->toBe(1)
        ->and($master->status->missing_assessments->first()->pivot->course_id)->toBe($testCourse->id);

    SeedService::deleteMaster($master);
});

