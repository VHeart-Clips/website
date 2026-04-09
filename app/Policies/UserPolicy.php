<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    public const int SystemUser = 0;

    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyUser);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(Permission::ViewAnyUser);
    }

    public function create(User $user): bool
    {
        // we get our supply of fresh users from twitch
        return false;
    }

    public function update(User $user, User $model): bool
    {
        if ($model->id === self::SystemUser) {
            return false;
        }

        $userRole = $user->getRole();

        if (! $userRole instanceof Role) {
            return false;
        }

        if ($userRole->weight <= $model->getRole()?->weight) {
            return $userRole->id === self::SystemUser;
        }

        return $user->can(Permission::UpdateAnyUser);
    }

    public function comment(User $user, User $model): bool
    {
        return $user->can(Permission::ViewAnyComment);
    }

    public function delete(User $user, User $model): Response
    {
        if ($model->id === self::SystemUser) {
            return $this->deny('System user can not be deleted');
        }

        if ($user->is($model)) {
            return $this->deny('Cannot delete own user');
        }

        $userRole = $user->getRole();

        if (! $userRole instanceof Role) {
            return $this->deny();
        }

        if ($userRole->id !== self::SystemUser && $userRole->weight <= $model->getRole()?->weight) {
            return $this->deny('Cannot delete users with equal or higher role weight');
        }

        if ($user->can(Permission::DeleteAnyUser)) {
            return $this->allow();
        }

        return $this->deny();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::DeleteAnyUser);
    }

    public function restore(User $user, User $model): bool
    {
        if ($model->id === self::SystemUser) {
            return false;
        }

        return $user->can(Permission::RestoreAnyUser);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(Permission::RestoreAnyUser);
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($model->id === self::SystemUser) {
            return false;
        }

        $userRole = $user->getRole();

        if (! $userRole instanceof Role) {
            return false;
        }

        if ($userRole->weight <= $model->getRole()?->weight) {
            return $userRole->id === self::SystemUser;
        }

        return $user->can(Permission::ForceDeleteAnyUser);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(Permission::ForceDeleteAnyUser);
    }
}
