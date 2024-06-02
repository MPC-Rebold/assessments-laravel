<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'avatar' => $this->faker->url(),
            'is_admin' => $this->faker->boolean(),
            'provider' => $this->faker->word(),
            'provider_id' => $this->faker->word(),
            'provider_token' => Str::random(10),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): UserFactory
    {
        return $this->state(fn () => ['is_admin' => true]);
    }

    public function nonAdmin(): UserFactory
    {
        return $this->state(fn () => ['is_admin' => false]);
    }
}
