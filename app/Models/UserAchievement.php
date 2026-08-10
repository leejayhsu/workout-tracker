<?php

namespace App\Models;

use Database\Factories\UserAchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    /** @use HasFactory<UserAchievementFactory> */
    use HasFactory;

    protected $fillable = ['achievement_id', 'unlocked_at', 'announced_at'];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'announced_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Achievement, $this> */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
