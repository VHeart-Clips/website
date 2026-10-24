<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserSettings\NewGameAvoidancePreference;
use Database\Factories\UserPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function showOnLeaderboard(): Attribute
    {
        return Attribute::get(static fn (?bool $value): bool => $value ?? true);
    }

    protected function avoidSpoilers(): Attribute
    {
        return Attribute::get(static fn (?bool $value): bool => $value ?? false);
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
