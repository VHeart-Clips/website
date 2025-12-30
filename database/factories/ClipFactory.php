<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clip>
 */
class ClipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'twitch_id' => fake()->uuid(),
            'title' => fake()->sentence(),
            'url' => fake()->url(),
            'broadcaster_id' => User::factory(),
            'creator_id' => User::factory(),
            'submitter_id' => User::factory(),
            'game_id' => Game::factory(),
            'duration' => fake()->randomFloat(2,5,30),
            'date' => fake()->dateTimeBetween('-1 year')
        ];
    }
}
