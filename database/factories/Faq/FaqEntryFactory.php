<?php

declare(strict_types=1);

namespace Database\Factories\Faq;

use App\Models\FaQ\FaqEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaqEntry>
 */
class FaqEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->translations(['en', 'de'], [
                $this->faker->unique()->sentence(),
                $this->faker->unique()->sentence(),
            ]),
            'body' => $this->translations(['en', 'de'], [
                $this->faker->paragraph(),
                $this->faker->paragraph(),
            ]),
            'order' => $this->faker->numberBetween(1, 10),
            'published_at' => $this->faker->date(),
        ];
    }
}
