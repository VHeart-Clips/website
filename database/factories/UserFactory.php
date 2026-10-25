<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
#[UseModel(User::class)]
class UserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = fake()->unique()->numberBetween();

        return [
            'id' => $id,
            'name' => fake()->name(),
            'email' => null,
            'avatar_url' => 'https://api.dicebear.com/9.x/pixel-art/svg?seed='.$id,
            'email_verified_at' => null,
            'twitch_refresh_token' => null,
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
            'has_email_authentication' => false,
            'language' => null,
        ];
    }

    public function withTwoFactor(?string $secret = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'app_authentication_secret' => $secret ?? resolve(AppAuthentication::class)->generateSecretKey(),
            'app_authentication_recovery_codes' => [],
        ]);
    }

    public function withTwoFactorRecoveryCodes(?array $recoveryCodes = null): self
    {
        return $this->state(fn (array $attributes): array => [
            'app_authentication_recovery_codes' => $recoveryCodes ?? resolve(AppAuthentication::class)->generateRecoveryCodes(),
        ]);
    }

    public function withVerifiedEmail(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email' => fake()->safeEmail(),
            'email_verified_at' => fake()->dateTime(),
        ]);
    }

    public function withUnverifiedEmail(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email' => fake()->safeEmail(),
            'email_verified_at' => null,
        ]);
    }
}
