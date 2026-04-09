<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyReport);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        if ($user->id === $report->user_id) {
            return true;
        }

        return $user->can(Permission::ViewAnyReport);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Everyone can report anything
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        return $user->can(Permission::UpdateAnyReport);
    }

    public function claim(User $user, Report $report): bool
    {
        return $user->can(Permission::UpdateAnyReport);
    }

    public function comment(User $user, Report $report): bool
    {
        return $user->can(Permission::ViewAnyComment);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->can(Permission::DeleteAnyReport);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->can(Permission::RestoreAnyReport);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->can(Permission::ForceDeleteAnyReport);
    }
}
