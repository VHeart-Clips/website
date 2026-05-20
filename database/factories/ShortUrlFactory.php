<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShortUrl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShortUrl>
 */
class ShortUrlFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->uuid(),
            'url' => fake()->url(),
            'clicks' => 0,
        ];
    }
}
