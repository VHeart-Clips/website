<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserSettings\NewGameAvoidancePreference;
use App\Models\Traits\User\HasUserPreferences;
use Database\Factories\UserPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @see HasUserPreferences for defaults and getters on the User model
 */
#[WithoutIncrementing]
class UserPreference extends Model
{
    /** @use HasFactory<UserPreferenceFactory> */
    use HasFactory;

    protected $primaryKey = 'user_id';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'show_on_leaderboard' => 'bool',
            'avoid_spoilers' => 'bool',
            'avoid_new_games' => NewGameAvoidancePreference::class,
        ];
    }
}
