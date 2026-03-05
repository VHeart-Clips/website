<?php

declare(strict_types=1);

namespace Database\Factories\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterTeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<BroadcasterTeamMember>
 */
class BroadcasterTeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'broadcaster_id' => Broadcaster::factory(),
            'user_id' => User::factory(),
            'permissions' => null,
        ];
    }

    /**
     * @param  array<BroadcasterPermission>|Collection<int, BroadcasterPermission>|BroadcasterPermission  $permissions
     */
    public function permissions(array|Collection|BroadcasterPermission $permissions): static
    {
        return $this->state(fn (array $attributes): array => [
            'permissions' => $permissions instanceof Collection ? $permissions : collect(is_array($permissions) ? $permissions : [$permissions]),
        ]);
    }

    public function broadcaster(Broadcaster|int $broadcaster): static
    {
        return $this->state(fn (array $attributes): array => [
            'broadcaster_id' => $broadcaster instanceof Broadcaster ? $broadcaster->id : $broadcaster,
        ]);
    }

    public function user(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }
}
