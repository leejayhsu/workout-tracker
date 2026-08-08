<?php

namespace App\Models;

use Database\Factories\WorkoutEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutEntry extends Model
{
    /** @use HasFactory<WorkoutEntryFactory> */
    use HasFactory;

    protected $fillable = ['workout_id', 'performed_on', 'notes'];

    protected function casts(): array
    {
        return ['performed_on' => 'date'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Workout, $this> */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /** @return HasMany<WorkoutEntryExercise, $this> */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutEntryExercise::class)->orderBy('position');
    }
}
