<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Master;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'title' => $this->faker->word(),
            'valid_students' => $this->faker->words(),
            'valid_assessments' => $this->faker->words(),
            'specification_grading' => $this->faker->boolean(),
            'specification_grading_threshold' => $this->faker->randomFloat(2, 0, 1),

            'master_id' => Master::factory(),
        ];
    }
}
