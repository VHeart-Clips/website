<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public const int SuperAdminRole = 0;

    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyRole);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can(Permission::ViewAnyRole);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CreateRole);
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->id === self::SuperAdminRole) {
            return false;
        }

        $userRole = $user->getRole();

        if (! $userRole instanceof Role) {
            return false;
        }

        if ($role->weight >= $userRole->weight) {
            return $userRole->id === self::SuperAdminRole;
        }

        return $user->can(Permission::UpdateAnyRole);
    }

    public function updateAny(User $user): bool
    {
        return $user->can(Permission::UpdateAnyRole);
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->id === self::SuperAdminRole) {
            return false;
        }

        $userRole = $user->getRole();

        if (! $userRole instanceof Role) {
            return false;
        }

        if ($role->weight >= $userRole->weight) {
            return $userRole->id === self::SuperAdminRole;
        }

        return $user->can(Permission::DeleteAnyRole);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::DeleteAnyRole);
    }

    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
