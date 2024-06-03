<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionUser;
use App\Models\User;
use App\Services\CanvasService;
use App\Services\SeedService;
use App\Services\SyncService;
use Livewire\Volt\Volt;
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

test('Submitting Assessment to Canvas submits positive point grade for Course with SpecificationSetting off', function () {
    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@');
    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));

    expect($testCourse->specification_grading)->toBeFalsy();

    $enrolledUser = User::factory()->nonAdmin()->create(['email' => config('canvas.testing_enrolled_user_email')]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();
    $assessmentCourse = $testCourse->assessmentCourses->first();

    QuestionUser::create([
        'user_id' => $enrolledUser->id,
        'question_id' => Question::first()->id,
        'master_id' => $master->id,
        'course_id' => $testCourse->id,
        'answer' => 'answer',
        'is_correct' => true,
    ]);

    $this->actingAs($enrolledUser);

    Volt::test('assessment.assessment-body', ['assessmentCourse' => $assessmentCourse])
        ->call('submitToCanvas');

    $gradeResponse = CanvasService::getGrade($testCourse->id, $assessmentCourse->assessment_canvas_id, $enrolledUser->canvas->canvas_id);
    expect((int) $gradeResponse->json()['entered_grade'])->toBe(1);

    SeedService::deleteMaster($master);
});

test('Submitting Assessment to Canvas submits 0 point grade for Course with SpecificationSetting off', function () {
    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@');
    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));

    expect($testCourse->specification_grading)->toBeFalsy();

    $enrolledUser = User::factory()->nonAdmin()->create(['email' => config('canvas.testing_enrolled_user_email')]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();
    $assessmentCourse = $testCourse->assessmentCourses->first();

    $this->actingAs($enrolledUser);

    Volt::test('assessment.assessment-body', ['assessmentCourse' => $assessmentCourse])
        ->call('submitToCanvas');

    $gradeResponse = CanvasService::getGrade($testCourse->id, $assessmentCourse->assessment_canvas_id, $enrolledUser->canvas->canvas_id);
    expect((int) $gradeResponse->json()['entered_grade'])->toBe(0);

    SeedService::deleteMaster($master);
});

test('Submitting Assessment to Canvas submits complete grade for Course with SpecificationSetting on', function () {
    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@');
    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));
    $testCourse->update(['specification_grading' => true, 'specification_grading_threshold' => 0.8]);

    expect($testCourse->specification_grading)->toBeTruthy();

    $enrolledUser = User::factory()->nonAdmin()->create(['email' => config('canvas.testing_enrolled_user_email')]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();
    $assessmentCourse = $testCourse->assessmentCourses->first();

    QuestionUser::create([
        'user_id' => $enrolledUser->id,
        'question_id' => Question::first()->id,
        'master_id' => $master->id,
        'course_id' => $testCourse->id,
        'answer' => 'answer',
        'is_correct' => true,
    ]);

    $this->actingAs($enrolledUser);

    Volt::test('assessment.assessment-body', ['assessmentCourse' => $assessmentCourse])
        ->call('submitToCanvas');

    $gradeResponse = CanvasService::getGrade($testCourse->id, $assessmentCourse->assessment_canvas_id, $enrolledUser->canvas->canvas_id);
    expect($gradeResponse->json()['entered_grade'])->toBe('complete');

    SeedService::deleteMaster($master);
});

test('Submitting Assessment to Canvas submits incomplete grade for Course with SpecificationSetting on', function () {
    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@');
    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));
    $testCourse->update(['specification_grading' => true, 'specification_grading_threshold' => 0.8]);

    expect($testCourse->specification_grading)->toBeTruthy();

    $enrolledUser = User::factory()->nonAdmin()->create(['email' => config('canvas.testing_enrolled_user_email')]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();
    $assessmentCourse = $testCourse->assessmentCourses->first();

    $this->actingAs($enrolledUser);

    Volt::test('assessment.assessment-body', ['assessmentCourse' => $assessmentCourse])
        ->call('submitToCanvas');

    $gradeResponse = CanvasService::getGrade($testCourse->id, $assessmentCourse->assessment_canvas_id, $enrolledUser->canvas->canvas_id);
    expect($gradeResponse->json()['entered_grade'])->toBe('incomplete');

    SeedService::deleteMaster($master);
});



