<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutEntryExerciseSet extends Model
{
    protected $fillable = ['position', 'reps', 'weight'];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<WorkoutEntryExercise, $this> */
    public function workoutEntryExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutEntryExercise::class);
    }
}
