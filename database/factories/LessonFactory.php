<?php

namespace Database\Factories;

use App\Enums\LessonTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
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
            'type' => fake()->randomElement(LessonTypeEnum::cases()),
            'overview' => fake()->sentence,
            'url' => fake()->url,
            'content' => null,
            'duration' => fake()->randomDigitNotNull(),
            'free' => fake()->boolean,
            'visible' => fake()->boolean,
        ];
    }
}
