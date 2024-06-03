<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\QuestionUser;
use App\Models\User;
use App\Services\SeedService;
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

test('Submitting Question answer creates question with correct answer in database', function () {
    $master = SeedService::createMaster('__NewMaster');
    $assessment = SeedService::createAssessment('__NewMaster', '__NewAssessment', 'question@@answer@@');
    $question = $assessment->questions->first();
    $course = Course::factory()->create();
    AssessmentCourse::factory()->create(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'assessment_canvas_id' => 1, 'due_at' => now()->addDay()]);

    $enrolledUser = User::factory()->nonAdmin()->create();
    $course->users()->attach($enrolledUser->id);

    $this->actingAs($enrolledUser);
    Volt::test('assessment.question', ['question' => $question, 'course' => $course])
        ->set('answer', 'answer')
        ->call('submit');

    expect(QuestionUser::count())->toBe(1)
        ->and(QuestionUser::first()->question_id)->toBe($question->id)
        ->and(QuestionUser::first()->answer)->toBe('answer')
        ->and(QuestionUser::first()->user_id)->toBe($enrolledUser->id)
        ->and(QuestionUser::first()->is_correct)->toBeTruthy();

    SeedService::deleteMaster($master);
});

test('Submitting Question answer creates question with incorrect answer in database', function () {
    $master = SeedService::createMaster('__NewMaster');
    $assessment = SeedService::createAssessment('__NewMaster', '__NewAssessment', 'question@@answer@@');
    $question = $assessment->questions->first();
    $course = Course::factory()->create();
    AssessmentCourse::factory()->create(['course_id' => $course->id, 'assessment_id' => $assessment->id, 'assessment_canvas_id' => 1, 'due_at' => now()->addDay()]);

    $enrolledUser = User::factory()->nonAdmin()->create();
    $course->users()->attach($enrolledUser->id);

    $this->actingAs($enrolledUser);
    Volt::test('assessment.question', ['question' => $question, 'course' => $course])
        ->set('answer', 'incorrect')
        ->call('submit');

    expect(QuestionUser::count())->toBe(1)
        ->and(QuestionUser::first()->question_id)->toBe($question->id)
        ->and(QuestionUser::first()->answer)->toBe('incorrect')
        ->and(QuestionUser::first()->user_id)->toBe($enrolledUser->id)
        ->and(QuestionUser::first()->is_correct)->toBeFalsy();

    SeedService::deleteMaster($master);
});
