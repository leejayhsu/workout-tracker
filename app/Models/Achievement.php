<?php

namespace App\Models;

use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'threshold',
        'is_secret',
        'thumbnail_path',
        'artwork_path',
    ];

    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
            'threshold' => 'integer',
        ];
    }

    /** @return HasMany<UserAchievement, $this> */
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }
}
