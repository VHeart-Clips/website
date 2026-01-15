<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClipVoteType;
use App\Models\Clip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clip_id' => Clip::factory(),
            'user_id' => User::factory(),
            'type' => ClipVoteType::Public,
            'voted' => fake()->boolean(),
        ];
    }
}
