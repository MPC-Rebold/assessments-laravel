<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Master;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'title' => $this->faker->word(),

            'master_id' => Master::factory(),
        ];
    }
}
