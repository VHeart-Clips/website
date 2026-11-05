<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Clips\ClipStatus;
use App\Models\Category;
use App\Models\Clip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clip>
 */
#[UseModel(Clip::class)]
class ClipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'twitch_id' => fake()->uuid(),
            'title' => fake()->sentence(),
            'broadcaster_id' => User::factory(),
            'creator_id' => User::factory(),
            'submitter_id' => User::factory(),
            'category_id' => Category::factory(),
            'duration' => fake()->randomFloat(2, 5, 30),
            'date' => fake()->dateTimeBetween('-1 year'),
            'thumbnail_url' => fake()->imageUrl(),
            'status' => ClipStatus::Unknown,
            'total_jury_votes' => 0,
            'total_public_votes' => 0,
            'total_votes' => 0,
            'total_impressions' => 0,
            'score' => 0,
        ];
    }
}
