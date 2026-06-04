<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ban;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ban>
 */
class BanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filterableClass = fake()->randomElement([
            User::class,
            Broadcaster::class,
        ]);

        $bannedUntil = fake()->randomElement([
            fake()->dateTime(now()->addYear()),
            null,
        ]);

        return [
            'admin_id' => User::factory(),
            'reason' => fake()->paragraph(),
            'bannable_id' => $filterableClass::factory(),
            'bannable_type' => (new $filterableClass)->getMorphClass(),
            'banned_until' => $bannedUntil,
            'unbanned_at' => null,
            'unbanned_by' => null,
        ];
    }
}
