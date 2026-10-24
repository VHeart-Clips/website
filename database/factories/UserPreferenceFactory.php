<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserSettings\NewGameAvoidancePreference;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'show_on_leaderboard' => null,
            'avoid_spoilers' => null,
            'avoid_new_games' => null,
        ];
    }

    public function withAvoidNewGames(?NewGameAvoidancePreference $preference = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'avoid_new_games' => $preference,
        ]);
    }
}
