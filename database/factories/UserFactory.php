<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = fake()->unique()->numberBetween();

        return [
            'id' => $id,
            'name' => fake()->name(),
            'email' => null,
            'clip_permission' => false,
            'avatar_url' => 'https://api.dicebear.com/9.x/pixel-art/svg?seed='.$id,
            'email_verified_at' => null,
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
        ];
    }

    public function withVerifiedEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->safeEmail(),
            'email_verified_at' => fake()->dateTime(),
        ]);
    }

    public function withUnverifiedEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->safeEmail(),
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
