<?php

namespace LaravelSmartSearch\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sharifuddin\LaravelSmartSearch\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
