<?php

use App\Achievements\AchievementEvaluator;
use App\Actions\CreateWorkoutEntry;
use App\Models\Achievement;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\AchievementSeeder;
use Livewire\Livewire;

function workoutFor(User $user): Workout
{
    $program = $user->programs()->create(['name' => 'Strength']);

    return $program->workouts()->create(['label' => 'A', 'position' => 0]);
}

test('the achievements page renders the seeded catalog and a users unlocks', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $firstAchievement = Achievement::query()->where('key', 'workout_entries.count.1')->firstOrFail();
    $user->userAchievements()->create([
        'achievement_id' => $firstAchievement->id,
        'unlocked_at' => now(),
    ]);

    Livewire::actingAs($user)->test('pages::achievements.index')
        ->assertOk()
        ->assertSee(asset('images/achievements/workout-count/001-thumbnail-v2.png'), escape: false)
        ->assertSee(asset('images/achievements/workout-count/003-thumbnail.png'), escape: false)
        ->assertSee('1 of 10 unlocked')
        ->assertSee('First Step')
        ->assertSee('Quarter Century')
        ->assertDontSee('Quarter Thousand')
        ->assertSee('data-flux-tooltip', escape: false)
        ->assertSee('md:hidden', escape: false)
        ->assertSee('hidden md:block', escape: false)
        ->assertSee('size-[100px]', escape: false)
        ->assertSee('grid-cols-[repeat(auto-fill,6.25rem)]', escape: false);
});

test('workout entry milestones award every eligible threshold without duplicates', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $workout = workoutFor($user);

    $user->workoutEntries()->createMany([
        ['workout_id' => $workout->id, 'performed_on' => '2026-08-01'],
        ['workout_id' => $workout->id, 'performed_on' => '2026-08-02'],
        ['workout_id' => $workout->id, 'performed_on' => '2026-08-03'],
    ]);

    $evaluator = app(AchievementEvaluator::class);
    $evaluator->evaluateWorkoutEntryMilestones($user);
    $evaluator->evaluateWorkoutEntryMilestones($user);

    expect($user->userAchievements()->count())->toBe(2)
        ->and($user->achievements()->orderBy('key')->pluck('key')->all())->toBe([
            'workout_entries.count.1',
            'workout_entries.count.3',
        ])
        ->and($user->userAchievements()->whereNull('announced_at')->count())->toBe(2);
});

test('creating a workout entry awards the first milestone', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $workout = workoutFor($user);

    app(CreateWorkoutEntry::class)->handle($user, $workout, [
        'performedOn' => '2026-08-01',
        'notes' => null,
        'exercises' => [],
    ]);

    expect($user->workoutEntries()->count())->toBe(1)
        ->and($user->achievements()->orderBy('key')->pluck('key')->all())->toBe(['workout_entries.count.1']);
});

test('achievement backfills are silent and safe to rerun', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $workout = workoutFor($user);
    $user->workoutEntries()->createMany([
        ['workout_id' => $workout->id, 'performed_on' => '2026-08-01'],
        ['workout_id' => $workout->id, 'performed_on' => '2026-08-02'],
        ['workout_id' => $workout->id, 'performed_on' => '2026-08-03'],
    ]);

    $this->artisan('achievements:backfill')->assertSuccessful();
    $this->artisan('achievements:backfill')->assertSuccessful();

    expect($user->userAchievements()->count())->toBe(2)
        ->and($user->userAchievements()->whereNull('announced_at')->count())->toBe(0);
});
