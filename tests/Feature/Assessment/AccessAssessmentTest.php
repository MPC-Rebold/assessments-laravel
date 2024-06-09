<?php

use App\Models\AssessmentCourse;
use App\Models\User;

test('active assessment with no due date allows access to enrolled student', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->nullDueDate()->active()->create();
    $assessmentCourse->course->enrollUser($user);

    $this->actingAs($user)
        ->get(route('assessment.show', [$assessmentCourse->course_id, $assessmentCourse->assessment_canvas_id]))
        ->assertOk();
});

test('active assessment with due date in future allows access to enrolled student', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->futureDueDate()->active()->create();
    $assessmentCourse->course->enrollUser($user);

    $this->actingAs($user)
        ->get(route('assessment.show', [$assessmentCourse->course_id, $assessmentCourse->assessment_canvas_id]))
        ->assertOk();
});

test('active assessment with due date in past allows practice access to enrolled student', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->pastDueDate()->active()->create();
    $assessmentCourse->course->enrollUser($user);

    $this->actingAs($user)
        ->get(route('assessment.show', [$assessmentCourse->course_id, $assessmentCourse->assessment_canvas_id]))
        ->assertOk()
        ->assertSee('PRACTICE MODE');
});

test('inactive assessment denies access to student', function () {
    $user = User::factory()->nonAdmin()->create();

    $assessmentCourse = AssessmentCourse::factory()->pastDueDate()->inactive()->create();
    $assessmentCourse->course->enrollUser($user);

    $this->actingAs($user)
        ->get(route('assessment.show', [$assessmentCourse->course_id, $assessmentCourse->assessment_canvas_id]))
        ->assertStatus(403);
});
