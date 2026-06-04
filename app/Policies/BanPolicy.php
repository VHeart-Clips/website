<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Ban;
use App\Models\User;

class BanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyBan);
    }

    public function view(User $user, Ban $ban): bool
    {
        return $user->can(Permission::ViewAnyBan);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CreateAnyBan);
    }

    public function update(User $user, Ban $ban): bool
    {
        return $user->can(Permission::UpdateAnyBan);
    }

    public function updateAny(User $user): bool
    {
        return $user->can(Permission::UpdateAnyBan);
    }

    public function delete(User $user, Ban $ban): bool
    {
        return $user->can(Permission::DeleteAnyBan);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::DeleteAnyBan);
    }

    public function restore(User $user, Ban $ban): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Ban $ban): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
