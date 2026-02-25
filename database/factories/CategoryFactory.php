<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(),
            'title' => fake()->paragraph(1),
            'box_art' => fake()->imageUrl(268, 357),
            'is_banned' => false,
        ];
    }

    public function isBanned(?bool $value = true): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_banned' => $value,
        ]);
    }
}
