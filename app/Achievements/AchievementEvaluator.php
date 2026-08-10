<?php

namespace App\Achievements;

use App\Models\Achievement;
use App\Models\User;

class AchievementEvaluator
{
    public function evaluateWorkoutEntryMilestones(User $user, bool $announce = true): void
    {
        $entryCount = $user->workoutEntries()->count();
        $now = now();

        $rows = Achievement::query()
            ->where('category', 'workout_entries')
            ->whereNotNull('threshold')
            ->where('threshold', '<=', $entryCount)
            ->get(['id'])
            ->map(fn (Achievement $achievement): array => [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'unlocked_at' => $now,
                'announced_at' => $announce ? null : $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows !== []) {
            $user->userAchievements()->insertOrIgnore($rows);
        }
    }
}
