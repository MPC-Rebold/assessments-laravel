<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'question' => $this->faker->word(),
            'answer' => $this->faker->word(),
            'max_attempts' => $this->faker->randomNumber(),
            'number' => $this->faker->randomNumber(),

            'assessment_id' => Assessment::factory(),
        ];
    }

    public function withAssessmentId($assessmentId): QuestionFactory
    {
        return $this->state(function () use ($assessmentId) {
            return [
                'assessment_id' => $assessmentId,
            ];
        });
    }
}
