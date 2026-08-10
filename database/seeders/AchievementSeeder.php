<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['key' => 'workout_entries.count.1', 'name' => 'First Step', 'description' => 'Complete your first workout entry.', 'threshold' => 1, 'thumbnail_path' => 'images/achievements/workout-count/001-thumbnail-v2.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.3', 'name' => 'Finding Your Rhythm', 'description' => 'Complete 3 workout entries.', 'threshold' => 3, 'thumbnail_path' => 'images/achievements/workout-count/003-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.5', 'name' => 'Five Strong', 'description' => 'Complete 5 workout entries.', 'threshold' => 5, 'thumbnail_path' => 'images/achievements/workout-count/005-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.10', 'name' => 'Double Digits', 'description' => 'Complete 10 workout entries.', 'threshold' => 10, 'thumbnail_path' => 'images/achievements/workout-count/010-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.25', 'name' => 'Quarter Century', 'description' => 'Complete 25 workout entries.', 'threshold' => 25, 'thumbnail_path' => 'images/achievements/workout-count/025-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.50', 'name' => 'Half Century', 'description' => 'Complete 50 workout entries.', 'threshold' => 50, 'thumbnail_path' => 'images/achievements/workout-count/050-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.100', 'name' => 'Centurion', 'description' => 'Complete 100 workout entries.', 'threshold' => 100, 'thumbnail_path' => 'images/achievements/workout-count/100-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.150', 'name' => 'Work Ethic', 'description' => 'Complete 150 workout entries.', 'threshold' => 150, 'thumbnail_path' => 'images/achievements/workout-count/150-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.200', 'name' => 'Two Hundred Club', 'description' => 'Complete 200 workout entries.', 'threshold' => 200, 'thumbnail_path' => 'images/achievements/workout-count/200-thumbnail.png', 'is_secret' => false],
            ['key' => 'workout_entries.count.250', 'name' => 'Quarter Thousand', 'description' => 'Complete 250 workout entries.', 'threshold' => 250, 'thumbnail_path' => null, 'is_secret' => true],
        ] as $achievement) {
            Achievement::query()->updateOrCreate(
                ['key' => $achievement['key']],
                [...$achievement, 'category' => 'workout_entries', 'artwork_path' => null],
            );
        }
    }
}
