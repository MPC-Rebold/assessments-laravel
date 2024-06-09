<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentCourse;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AssessmentCourseFactory extends Factory
{
    protected $model = AssessmentCourse::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'assessment_canvas_id' => $this->faker->randomNumber(),
            'due_at' => Carbon::now(),
            'is_active' => $this->faker->boolean(),

            'assessment_id' => Assessment::factory(),
            'course_id' => Course::factory(),
        ];
    }

    public function active(): AssessmentCourseFactory
    {
        return $this->state(function () {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive(): AssessmentCourseFactory
    {
        return $this->state(function () {
            return [
                'is_active' => false,
            ];
        });
    }

    public function pastDueDate(): AssessmentCourseFactory
    {
        return $this->state(function () {
            return [
                'due_at' => Carbon::now()->subYear(),
            ];
        });
    }

    public function futureDueDate(): AssessmentCourseFactory
    {
        return $this->state(function () {
            return [
                'due_at' => Carbon::now()->addYear(),
            ];
        });
    }

    public function nullDueDate(): AssessmentCourseFactory
    {
        return $this->state(function () {
            return [
                'due_at' => null,
            ];
        });
    }
}
