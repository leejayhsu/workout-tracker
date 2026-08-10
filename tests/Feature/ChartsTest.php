<?php

use App\Models\User;
use App\Models\Workout;

function chartWorkoutFor(User $user): Workout
{
    $program = $user->programs()->create(['name' => 'Strength']);

    return $program->workouts()->create(['label' => 'A', 'position' => 0]);
}

function recordExercise(User $user, Workout $workout, string $date, string $exerciseKey, string $exerciseName, float $weight, int $reps = 1, string $unit = 'kg'): void
{
    $entry = $user->workoutEntries()->create([
        'workout_id' => $workout->id,
        'performed_on' => $date,
    ]);
    $exercise = $entry->exercises()->create([
        'exercise_key' => $exerciseKey,
        'exercise_name' => $exerciseName,
        'position' => 0,
        'weight_unit' => $unit,
    ]);
    $exercise->sets()->create([
        'position' => 0,
        'reps' => $reps,
        'weight' => $weight,
    ]);
}

test('guests are redirected to the login page', function () {
    $this->get(route('charts.index'))->assertRedirect(route('login'));
});

test('charts show the highest weight from each eligible workout entry', function () {
    $user = User::factory()->create();
    $workout = chartWorkoutFor($user);

    recordExercise($user, $workout, '2026-08-01', 'barbell_back_squat', 'Barbell back squat', 100);
    recordExercise($user, $workout, '2026-08-03', 'barbell_back_squat', 'Barbell back squat', 105);
    recordExercise($user, $workout, '2026-08-05', 'barbell_back_squat', 'Barbell back squat', 110);

    $this->actingAs($user)
        ->get(route('charts.index'))
        ->assertOk()
        ->assertSee('Barbell back squat')
        ->assertSee('3 workout entries')
        ->assertSee('110 KG')
        ->assertSee('2026-08-01T00:00:00Z', escape: false)
        ->assertSee('2026-08-05', escape: false);
});

test('charts exclude exercises with fewer than three recorded workout entries and other users data', function () {
    $user = User::factory()->create();
    $workout = chartWorkoutFor($user);
    $otherUser = User::factory()->create();
    $otherWorkout = chartWorkoutFor($otherUser);

    recordExercise($user, $workout, '2026-08-01', 'deadlift', 'Deadlift', 120);
    recordExercise($user, $workout, '2026-08-03', 'deadlift', 'Deadlift', 125);
    recordExercise($otherUser, $otherWorkout, '2026-08-01', 'barbell_bench_press', 'Private bench press', 80);
    recordExercise($otherUser, $otherWorkout, '2026-08-03', 'barbell_bench_press', 'Private bench press', 85);
    recordExercise($otherUser, $otherWorkout, '2026-08-05', 'barbell_bench_press', 'Private bench press', 90);

    $this->actingAs($user)
        ->get(route('charts.index'))
        ->assertOk()
        ->assertSee('No charts yet')
        ->assertDontSee('Deadlift')
        ->assertDontSee('Private bench press');
});

test('charts only include sets with at least one rep', function () {
    $user = User::factory()->create();
    $workout = chartWorkoutFor($user);

    recordExercise($user, $workout, '2026-08-01', 'barbell_row', 'Barbell row', 80);
    recordExercise($user, $workout, '2026-08-03', 'barbell_row', 'Barbell row', 85);
    recordExercise($user, $workout, '2026-08-05', 'barbell_row', 'Barbell row', 90, reps: 0);

    $this->actingAs($user)
        ->get(route('charts.index'))
        ->assertOk()
        ->assertSee('No charts yet')
        ->assertDontSee('Barbell row');
});
