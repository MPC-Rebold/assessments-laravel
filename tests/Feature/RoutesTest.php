<?php

use App\Models\AssessmentCourse;
use App\Models\Course;
use App\Models\Question;
use App\Models\Status;
use App\Models\User;

test('GET / is 302 for non-authenticated', function () {
    $this->get('/')->assertStatus(302);
});

test('GET dashboard is 200', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('dashboard'))
        ->assertOk();
});

test('GET profile is 200', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('profile'))
        ->assertOk();
});

test('GET course.index is 200', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('course.index'))
        ->assertOk();
});

test('GET course.show is 200 for enrolled user', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    $course->users()->attach($user);

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('course.show', ['courseId' => $course->id]))
        ->assertOk();
});

test('GET course.show is 302 for non-enrolled user', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('course.show', ['courseId' => $course->id]))
        ->assertStatus(302);
});

test('GET assessment.show is 200 for enrolled user on active course', function () {
    $user = User::factory()->create();

    $assessmentCourse = AssessmentCourse::factory()->active()->create();
    $course = $assessmentCourse->course;
    $course->users()->attach($user);

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('assessment.show', ['courseId' => $course->id, 'assessmentId' => $assessmentCourse->assessment_canvas_id]))
        ->assertOk();
});

test('GET assessment.show is 403 for enrolled user on inactive course', function () {
    $user = User::factory()->create();

    $assessmentCourse = AssessmentCourse::factory()->inactive()->create();
    $course = $assessmentCourse->course;
    $course->users()->attach($user);

    $this->actingAs($user)
        ->withSession(['foo' => 'bar'])
        ->get(route('assessment.show', ['courseId' => $course->id, 'assessmentId' => $assessmentCourse->assessment_canvas_id]))
        ->assertStatus(403);
});

test('GET admin is 200 for admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('admin'))
        ->assertOk();
});

test('GET admin is 403 for non-admin', function () {
    $nonAdmin = User::factory()->nonAdmin()->create();

    $this->actingAs($nonAdmin)
        ->withSession(['foo' => 'bar'])
        ->get(route('admin'))
        ->assertStatus(403);
});

test('GET master.edit is 200 for admin', function () {
    $admin = User::factory()->admin()->create();
    $status = Status::factory()->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('master.edit', ['masterId' => $status->master->id]))
        ->assertOk();
});

test('GET course.edit is 200 for admin', function () {
    $admin = User::factory()->admin()->create();
    $status = Status::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('course.edit', ['masterId' => $status->master->id, 'courseId' => $course->id]))
        ->assertOk();
});

test('GET assessment.edit is 200 for admin', function () {
    $admin = User::factory()->admin()->create();
    $status = Status::factory()->create();
    $assessment = AssessmentCourse::factory()->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('assessment.edit', ['masterId' => $status->master->id, 'assessmentId' => $assessment->id]))
        ->assertOk();
});

test('GET user.index is 200 for admin', function () {
    $admin = User::factory()->admin()->create();
    $courses = Course::factory()->count(3)->create();

    foreach ($courses as $course) {
        $course->users()->attach(User::factory()->create());
    }

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('user.index'))
        ->assertOk();
});

test('GET user.show is 200 for admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('user.show', ['userId' => $user->id]))
        ->assertOk();
});

test('GET user.grade.show is 200 for admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $assessment = AssessmentCourse::factory()->create();
    Question::factory()->withAssessmentId($assessment->assessment->id)->count(3)->create();

    $this->actingAs($admin)
        ->withSession(['foo' => 'bar'])
        ->get(route('user.grade.show', ['userId' => $user->id, 'assessmentId' => $assessment->id]))
        ->assertOk();
});
