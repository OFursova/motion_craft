<?php

namespace Database\Factories;

use App\Enums\LevelEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'overview' => fake()->sentence,
            'description' => fake()->realText(100),
            'cover' => fake()->imageUrl(),
            'level' => fake()->randomElement(LevelEnum::cases()),
            'free' => fake()->boolean,
            'visible' => fake()->boolean,
        ];
    }
}
