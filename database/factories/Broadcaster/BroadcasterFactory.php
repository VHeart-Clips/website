<?php

declare(strict_types=1);

namespace Database\Factories\Broadcaster;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Clips\ClipStatus;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Broadcaster>
 */
#[UseModel(Broadcaster::class)]
class BroadcasterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => User::factory(),
            'consent' => null,
            'twitch_mod_permissions' => null,
            'submit_user_allowed' => false,
            'submit_mods_allowed' => false,
            'submit_vip_allowed' => false,
            'default_clip_status' => ClipStatus::Unknown,
        ];
    }

    /**
     * @param  array<BroadcasterConsent>|Collection<int, BroadcasterConsent>|BroadcasterConsent  $consents
     */
    public function withConsent(array|Collection|BroadcasterConsent $consents): static
    {
        return $this->state(fn (array $attributes): array => [
            'consent' => $consents instanceof Collection ? $consents : collect(is_array($consents) ? $consents : [$consents]),
        ]);
    }

    /**
     * @param  array<BroadcasterPermission>|Collection<int, BroadcasterPermission>|BroadcasterPermission  $permissions
     */
    public function withTwitchModPermissions(array|Collection|BroadcasterPermission $permissions): static
    {
        return $this->state(fn (array $attributes): array => [
            'twitch_mod_permissions' => $permissions instanceof Collection ? $permissions : collect(is_array($permissions) ? $permissions : [$permissions]),
        ]);
    }

    public function allowsUserSubmissions(): static
    {
        return $this->state(fn (array $attributes): array => [
            'submit_user_allowed' => true,
        ]);
    }

    public function allowsModSubmissions(): static
    {
        return $this->state(fn (array $attributes): array => [
            'submit_mods_allowed' => true,
        ]);
    }

    public function allowsVipSubmissions(): static
    {
        return $this->state(fn (array $attributes): array => [
            'submit_vip_allowed' => true,
        ]);
    }

    public function allowsSubmissions(): static
    {
        return $this->state(fn (array $attributes): array => [
            'submit_user_allowed' => true,
            'submit_mods_allowed' => true,
            'submit_vip_allowed' => true,
        ]);
    }
}
