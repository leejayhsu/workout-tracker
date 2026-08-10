<?php

namespace App\Console\Commands;

use App\Achievements\AchievementEvaluator;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('achievements:backfill')]
#[Description('Silently award eligible achievements to existing users')]
class BackfillAchievements extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AchievementEvaluator $achievementEvaluator): int
    {
        User::query()->chunkById(100, function ($users) use ($achievementEvaluator): void {
            $users->each(fn (User $user) => $achievementEvaluator->evaluateWorkoutEntryMilestones($user, announce: false));
        });

        $this->components->info('Achievements backfilled.');

        return self::SUCCESS;
    }
}
