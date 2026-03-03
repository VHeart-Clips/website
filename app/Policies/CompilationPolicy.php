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
    public function view(User $user, Compilation $compilation): Response
    {
        if ($user->id === $compilation->user_id) {
            return $this->allow('owner can view compilation');
        }

        if ($user->can(Permission::ViewAnyCompilation)) {
            return $this->allow('user can view any compilation');
        }

        return $this->denyAsNotFound();
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
    public function update(User $user, Compilation $compilation): Response
    {
        if ($user->id === $compilation->user_id) {
            return $this->allow('owner can update compilation');
        }

        if ($user->can(Permission::UpdateAnyCompilation)) {
            return $this->allow('user can update any compilation');
        }

        return $this->denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Compilation $compilation): Response
    {
        if ($user->id === $compilation->user_id) {
            return $this->allow('owner can delete compilation');
        }

        if ($user->can(Permission::DeleteAnyCompilation)) {
            return $this->allow('user can delete any compilation');
        }

        return $this->denyAsNotFound();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Compilation $compilation): Response
    {
        if ($user->id === $compilation->user_id) {
            return $this->allow('owner can restore compilation');
        }

        if ($user->can(Permission::RestoreAnyCompilation)) {
            return $this->allow('user can restore any compilation');
        }

        return $this->denyAsNotFound();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Compilation $compilation): Response
    {
        if ($user->can(Permission::ForceDeleteAnyCompilation)) {
            return $this->allow('user can force delete any compilation');
        }

        return $this->denyAsNotFound();
    }
}
