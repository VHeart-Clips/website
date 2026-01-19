<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Clip\Compilation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CompilationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyCompilation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Compilation $compilation): Response|bool
    {
        if ($user->id === $compilation->user_id) {
            return true;
        }

        return $user->can(Permission::ViewAnyCompilation) || $this->denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::CreateCompilation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Compilation $compilation): bool
    {
        return $user->can(Permission::UpdateAnyCompilation);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Compilation $compilation): bool
    {
        return $user->can(Permission::DeleteAnyCompilation);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Compilation $compilation): bool
    {
        return $user->can(Permission::RestoreAnyCompilation);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Compilation $compilation): bool
    {
        return $user->can(Permission::ForceDeleteAnyCompilation);
    }
}
