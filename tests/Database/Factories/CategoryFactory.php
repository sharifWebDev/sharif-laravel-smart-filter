<?php

namespace LaravelSmartSearch\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sharifuddin\LaravelSmartSearch\Tests\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
        ];
    }
}
