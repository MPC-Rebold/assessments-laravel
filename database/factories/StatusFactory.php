<?php

namespace Database\Factories;

use App\Models\Master;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StatusFactory extends Factory
{
    protected $model = Status::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'has_seed' => $this->faker->boolean(),

            'master_id' => Master::factory(),
        ];
    }
}
