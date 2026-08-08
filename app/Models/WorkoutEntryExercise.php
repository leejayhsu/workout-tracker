<?php

namespace App\Models;

use App\WeightUnit;
use Database\Factories\WorkoutEntryExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutEntryExercise extends Model
{
    /** @use HasFactory<WorkoutEntryExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_key', 'exercise_name', 'position', 'weight_unit',
    ];

    protected function casts(): array
    {
        return [
            'weight_unit' => WeightUnit::class,
        ];
    }

    /** @return BelongsTo<WorkoutEntry, $this> */
    public function workoutEntry(): BelongsTo
    {
        return $this->belongsTo(WorkoutEntry::class);
    }

    /** @return HasMany<WorkoutEntryExerciseSet, $this> */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutEntryExerciseSet::class)->orderBy('position');
    }
}
