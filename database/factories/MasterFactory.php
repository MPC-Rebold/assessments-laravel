<?php

namespace Database\Factories;

use App\Models\Master;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MasterFactory extends Factory
{
    protected $model = Master::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'title' => $this->faker->unique()->word(),
        ];
    }
}
