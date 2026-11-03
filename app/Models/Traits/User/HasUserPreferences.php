<?php

declare(strict_types=1);

namespace App\Models\Traits\User;

use App\Enums\UserSettings\NewGameAvoidancePreference;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @phpstan-require-extends User
 *
 * @phpstan-type UserPreferencesShape array{
 *      show_on_leaderboard?: bool,
 *      avoid_spoilers?: bool,
 *      avoid_new_games?: ?NewGameAvoidancePreference
 * }
 */
trait HasUserPreferences
{
    public const array DEFAULT_PREFERENCES = [
        'show_on_leaderboard' => true,
        'avoid_spoilers' => false,
        'avoid_new_games' => null,
    ];

    /**
     * @return HasOne<UserPreference, $this>
     */
    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class, 'user_id', 'id');
    }

    public function showsOnLeaderboardPreference(): bool
    {
        return $this->preferences?->show_on_leaderboard
            ?? self::DEFAULT_PREFERENCES['show_on_leaderboard'];
    }

    public function avoidsSpoilersPreference(): bool
    {
        return $this->preferences?->avoid_spoilers
            ?? self::DEFAULT_PREFERENCES['avoid_spoilers'];
    }

    public function avoidNewGamesPreference(): ?NewGameAvoidancePreference
    {
        return $this->preferences?->avoid_new_games
            ?? self::DEFAULT_PREFERENCES['avoid_new_games'];
    }

    /**
     * Updates (or creates) the user preferences
     *
     * @param  UserPreferencesShape  $attributes
     */
    public function updatePreferences(array $attributes): self
    {
        $newPreference = $this->preferences()->updateOrCreate(
            ['user_id' => $this->id],
            $attributes
        );

        $this->setRelation('preferences', $newPreference);

        return $this;
    }
}
