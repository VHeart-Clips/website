<?php

declare(strict_types=1);

namespace App\Policies\Broadcaster;

use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;

class BroadcasterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyBroadcaster);
    }

    public function view(User $user, Broadcaster $broadcaster): bool
    {
        return $user->can(Permission::ViewAnyBroadcaster);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CreateBroadcaster);
    }

    public function importUser(User $user): bool
    {
        return $user->can(Permission::CanImportUsers);
    }

    public function update(User $user, Broadcaster $broadcaster): bool
    {
        return $user->can(Permission::UpdateAnyBroadcaster);
    }

    public function delete(User $user, Broadcaster $broadcaster): bool
    {
        return $user->can(Permission::DeleteAnyBroadcaster);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::DeleteAnyBroadcaster);
    }

    public function restore(User $user, Broadcaster $broadcaster): bool
    {
        return $user->can(Permission::RestoreAnyBroadcaster);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(Permission::RestoreAnyBroadcaster);
    }

    public function forceDelete(User $user, Broadcaster $broadcaster): bool
    {
        return $user->can(Permission::ForceDeleteAnyBroadcaster);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(Permission::ForceDeleteAnyBroadcaster);
    }
}
