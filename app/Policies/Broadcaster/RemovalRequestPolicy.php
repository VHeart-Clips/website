<?php

declare(strict_types=1);

namespace App\Policies\Broadcaster;

use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\RemovalRequest;
use App\Models\User;

class RemovalRequestPolicy
{
    public function viewAny(User $user, Broadcaster|User|null $broadcaster = null): bool
    {
        if ($user->id === $broadcaster?->id) {
            return true;
        }

        return $user->can(Permission::ViewAnyRemovalRequest);
    }

    public function view(User $user, RemovalRequest $removalRequest): bool
    {
        if ($user->id === $removalRequest->broadcaster_id) {
            return true;
        }

        return $user->can(Permission::ViewAnyRemovalRequest);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RemovalRequest $removalRequest): bool
    {
        return $user->can(Permission::UpdateAnyRemovalRequest);
    }

    public function delete(User $user, RemovalRequest $removalRequest): bool
    {
        if ($user->id === $removalRequest->broadcaster_id) {
            return true;
        }

        return $user->can(Permission::DeleteAnyRemovalRequest);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::DeleteAnyRemovalRequest);
    }

    public function restore(User $user, RemovalRequest $removalRequest): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, RemovalRequest $removalRequest): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
