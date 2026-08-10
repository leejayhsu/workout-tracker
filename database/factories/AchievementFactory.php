<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'category' => 'workout_entries',
            'threshold' => fake()->numberBetween(1, 250),
            'is_secret' => false,
            'thumbnail_path' => null,
            'artwork_path' => null,
        ];
    }
}
