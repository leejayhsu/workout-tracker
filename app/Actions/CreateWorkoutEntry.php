<?php

namespace App\Actions;

use App\Achievements\AchievementEvaluator;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutEntry;
use Illuminate\Support\Facades\DB;

class CreateWorkoutEntry
{
    public function __construct(private AchievementEvaluator $achievementEvaluator) {}

    /**
     * @param  array{performedOn: string, notes: string|null, exercises: array<int, array{exercise_key: string, exercise_name: string, sets: array<int, array{reps: int, weight: float|int|string|null}>, weight_unit: string|null}>}  $attributes
     */
    public function handle(User $user, Workout $workout, array $attributes): WorkoutEntry
    {
        return DB::transaction(function () use ($attributes, $user, $workout): WorkoutEntry {
            $entry = $user->workoutEntries()->create([
                'workout_id' => $workout->id,
                'performed_on' => $attributes['performedOn'],
                'notes' => $attributes['notes'] ?: null,
            ]);

            foreach ($attributes['exercises'] as $position => $exercise) {
                $sets = $exercise['sets'];
                unset($exercise['sets']);
                $entryExercise = $entry->exercises()->create([...$exercise, 'position' => $position]);
                $entryExercise->sets()->createMany(array_map(
                    fn (array $set, int $setPosition): array => [...$set, 'position' => $setPosition],
                    $sets,
                    array_keys($sets),
                ));
            }

            $this->achievementEvaluator->evaluateWorkoutEntryMilestones($user);

            return $entry;
        });
    }
}
