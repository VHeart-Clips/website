<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/*
 * Here we can later check if the user is authorized to do stuff with this model (in this case user too, renamed to $model)
 * We can do a simple role check to verifying that the model cannot delete itself or the last thing of something
 * this may or may not be used for permission validation.
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyUser);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can(Permission::ViewUser);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // we get our supply of fresh users from twitch
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(Permission::UpdateAnyUser);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        if ($user->is($model)) {
            return $this->deny('Cannot delete own user');
        }

        if ($user->can(Permission::DeleteAnyUser)) {
            return $this->allow();
        }

        return $this->deny();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can(Permission::RestoreAnyUser);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can(Permission::ForceDeleteAnyUser);
    }
}
