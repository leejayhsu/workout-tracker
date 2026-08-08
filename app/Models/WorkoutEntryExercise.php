<?php

namespace App\Models;

use App\WeightUnit;
use Database\Factories\WorkoutEntryExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutEntryExercise extends Model
{
    /** @use HasFactory<WorkoutEntryExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_key', 'exercise_name', 'position', 'sets', 'reps', 'weight', 'weight_unit',
        'weight_mode', 'bar_weight', 'plate_counts',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'weight_unit' => WeightUnit::class,
            'bar_weight' => 'decimal:2',
            'plate_counts' => 'array',
        ];
    }

    /** @return BelongsTo<WorkoutEntry, $this> */
    public function workoutEntry(): BelongsTo
    {
        return $this->belongsTo(WorkoutEntry::class);
    }
}
