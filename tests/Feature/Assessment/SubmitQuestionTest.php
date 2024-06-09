<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\AssessmentCourse;
use App\Models\Question;
use App\Models\QuestionUser;
use App\Models\User;
use Livewire\Volt\Volt;

test('submitting question in active assessment creates QuestionUser for correct answer in database', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->futureDueDate()->active()->create();
    $assessment = $assessmentCourse->assessment;
    $assessment->questions()->save(Question::factory()->withAssessmentId($assessment->id)->create());
    $question = $assessment->questions->first();

    $this->actingAs($user);

    Volt::test('assessment.question', ['question' => $question, 'course' => $assessmentCourse->course])
        ->set('answer', $question->answer)
        ->call('submit');

    expect(QuestionUser::count())->toBe(1)
        ->and(QuestionUser::first()->question_id)->toBe($question->id)
        ->and(QuestionUser::first()->answer)->toBe($question->answer)
        ->and(QuestionUser::first()->user_id)->toBe($user->id)
        ->and(QuestionUser::first()->is_correct)->toBeTruthy();
});

test('submitting question in active assessment creates QuestionUser for incorrect answer in database', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->futureDueDate()->active()->create();
    $assessment = $assessmentCourse->assessment;
    $assessment->questions()->save(Question::factory()->withAssessmentId($assessment->id)->create());
    $question = $assessment->questions->first();

    $this->actingAs($user);

    $incorrectAnswer = $question->answer . '_';

    Volt::test('assessment.question', ['question' => $question, 'course' => $assessmentCourse->course])
        ->set('answer', $incorrectAnswer)
        ->call('submit');

    expect(QuestionUser::count())->toBe(1)
        ->and(QuestionUser::first()->question_id)->toBe($question->id)
        ->and(QuestionUser::first()->answer)->toBe($incorrectAnswer)
        ->and(QuestionUser::first()->user_id)->toBe($user->id)
        ->and(QuestionUser::first()->is_correct)->toBeFalsy();
});

test('submitting question on past due assessment does not affect database', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->pastDueDate()->active()->create();
    $assessment = $assessmentCourse->assessment;
    $assessment->questions()->save(Question::factory()->withAssessmentId($assessment->id)->create());
    $question = $assessment->questions->first();

    $this->actingAs($user);

    Volt::test('assessment.question', ['question' => $question, 'course' => $assessmentCourse->course])
        ->set('answer', $question->answer)
        ->call('submit');

    expect(QuestionUser::count())->toBe(0);
});

test('submitting question in practice mode does not affect database', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->pastDueDate()->active()->create();
    $assessment = $assessmentCourse->assessment;
    $assessment->questions()->save(Question::factory()->withAssessmentId($assessment->id)->create());
    $question = $assessment->questions->first();

    $this->actingAs($user);

    Volt::test('assessment.question', ['question' => $question, 'course' => $assessmentCourse->course])
        ->set('answer', $question->answer)
        ->call('practiceSubmit');

    expect(QuestionUser::count())->toBe(0);
});
