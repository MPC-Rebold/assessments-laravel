<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionUser;
use App\Models\User;
use App\Services\CanvasService;
use App\Services\SeedService;
use App\Services\SyncService;
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

test('turning SpecificationGrading on adjusts Course settings', function () {
    $course = Course::factory()->create(['specification_grading' => false, 'specification_grading_threshold' => -1]);

    Livewire::test('admin.specification-setting', ['course' => $course])
        ->set('specificationGradingThreshold', '65%')
        ->call('updateSpecificationGrading');

    $course->refresh();

    expect($course->specification_grading)->toBeTruthy()
        ->and($course->specification_grading_threshold)->toBe(0.65);
});

test('turning SpecificationGrading off adjusts Course settings', function () {
    $course = Course::factory()->create(['specification_grading' => true, 'specification_grading_threshold' => 0.65]);

    Livewire::test('admin.specification-setting', ['course' => $course])
        ->set('specificationGradingThreshold', 'OFF')
        ->call('updateSpecificationGrading');

    $course->refresh();

    expect($course->specification_grading)->toBeFalsy()
        ->and($course->specification_grading_threshold)->toBe(-1.0);
});

test('turning SpecificationGrading on regrades connected AssessmentCourses on Canvas', function () {
    $reset = CanvasService::editAssignment([config('canvas.testing_course_id'), config('canvas.testing_assessment_id')], ['points_possible' => 0.0, 'grading_type' => 'points']);
    expect($reset->status())->toBe(200);

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@');

    SyncService::syncCourses();

    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));
    expect($testCourse->specification_grading)->toBeFalsy();
    $enrolledUser = User::factory()->nonAdmin()->create(['email' => config('canvas.testing_enrolled_user_email')]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->refresh();

    QuestionUser::create([
        'user_id' => $enrolledUser->id,
        'question_id' => Question::first()->id,
        'master_id' => $master->id,
        'course_id' => $testCourse->id,
        'answer' => 'answer',
        'is_correct' => true,
    ]);

    $assessmentCourse = $testCourse->assessmentCourses->first();
    expect($assessmentCourse->gradeForUser($enrolledUser))->toBe(1);

    Livewire::test('admin.specification-setting', ['course' => $testCourse])
        ->set('specificationGradingThreshold', '65%')
        ->call('updateSpecificationGrading');

    $testCourse->refresh();
    $assessmentCourse->refresh();

    expect($testCourse->specification_grading)->toBeTruthy()
        ->and($assessmentCourse->gradeForUser($enrolledUser))->toBe('complete');

    $submission = CanvasService::getGrade($testCourse->id, $assessmentCourse->assessment_canvas_id, $enrolledUser->canvas->canvas_id)->json();
    expect($submission['entered_grade'])->toBe('complete');

    SeedService::deleteMaster($master);
})->group('sync');

test('turning SpecificationGrading off regrades connected AssessmentCourses on Canvas', function () {
    $reset = CanvasService::editAssignment([config('canvas.testing_course_id'), config('canvas.testing_assessment_id')], ['points_possible' => 0.0, 'grading_type' => 'pass_fail']);
    expect($reset->status())->toBe(200);

    SyncService::syncCourses();

    $master = SeedService::createMaster('__NewMaster');
    SeedService::createAssessment('__NewMaster', config('canvas.testing_assessment_name'), 'question@@answer@@');
    $testCourse = Course::firstWhere('title', config('canvas.testing_course_name'));

    $enrolledUser = User::factory()->nonAdmin()->create(['email' => config('canvas.testing_enrolled_user_email')]);

    SyncService::updateConnectedCourses($master, collect([$testCourse]));

    $testCourse->update(['specification_grading' => true, 'specification_grading_threshold' => 0.65]);

    QuestionUser::create([
        'user_id' => $enrolledUser->id,
        'question_id' => Question::first()->id,
        'master_id' => $master->id,
        'course_id' => $testCourse->id,
        'answer' => 'answer',
        'is_correct' => true,
    ]);

    $assessmentCourse = $testCourse->assessmentCourses->first();
    expect($assessmentCourse->gradeForUser($enrolledUser))->toBe('complete');

    Livewire::test('admin.specification-setting', ['course' => $testCourse])
        ->set('specificationGradingThreshold', 'OFF')
        ->call('updateSpecificationGrading');

    $submission = CanvasService::getGrade($testCourse->id, $assessmentCourse->assessment_canvas_id, $enrolledUser->canvas->canvas_id)->json();
    expect((int) $submission['entered_grade'])->toBe(1);

    SeedService::deleteMaster($master);
})->group('sync');
