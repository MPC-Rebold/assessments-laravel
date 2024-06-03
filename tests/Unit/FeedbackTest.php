<?php

use App\Models\QuestionUser;

test('feedback identifies correct characters', function () {
    $userAnswer = 'abc';
    $correctAnswer = 'abc';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><keep__>b</keep__><keep__>c</keep__>');
});

test('feedback identifies characters needing deletion', function () {
    $userAnswer = 'abc1';
    $correctAnswer = 'abc';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><keep__>b</keep__><keep__>c</keep__><delete__>1</delete__>');
});

test('feedback identifies missing characters', function () {
    $userAnswer = 'ac';
    $correctAnswer = 'abc';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><missing__>_</missing__><keep__>c</keep__>');
});

test('feedback allows missing optional spaces', function () {
    $userAnswer = 'ac';
    $correctAnswer = 'a c';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><keep__>c</keep__>');
});

test('feedback allows keeping optional spaces', function () {
    $userAnswer = 'a c';
    $correctAnswer = 'a c';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><keep__>&nbsp;</keep__><keep__>c</keep__>');
});

test('feedback catches too many optional spaces', function () {
    $userAnswer = 'a  c';
    $correctAnswer = 'a c';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><keep__>&nbsp;</keep__><delete__>&nbsp;</delete__><keep__>c</keep__>');
});

test('feedback catches missing required space', function () {
    $userAnswer = 'ac';
    $correctAnswer = 'a_c';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><missing__>_</missing__><keep__>c</keep__>');
});

test('feedback marks present required space as correct', function () {
    $userAnswer = 'a c';
    $correctAnswer = 'a_c';

    $feedback = QuestionUser::calculateFeedbackHelper($userAnswer, $correctAnswer);

    expect($feedback)->toBe('<keep__>a</keep__><keep__>&nbsp;</keep__><keep__>c</keep__>');
});
