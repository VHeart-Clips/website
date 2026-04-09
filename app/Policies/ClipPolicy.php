<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip;
use App\Models\User;

class ClipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyClip);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Clip $clip): bool
    {
        return $user->can(Permission::ViewAnyClip);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::CreateClip);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Clip $clip): bool
    {
        return $user->can(Permission::UpdateAnyClip);
    }

    public function comment(User $user, Clip $clip): bool
    {
        return $user->can(Permission::ViewAnyComment);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Clip $clip): bool
    {
        return $user->can(Permission::DeleteAnyClip);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Clip $clip): bool
    {
        return $user->can(Permission::RestoreAnyClip);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Clip $clip): bool
    {
        return $user->can(Permission::ForceDeleteAnyClip);
    }

    public function submit(User $user, ?Clip $clip = null): bool
    {
        return empty($user->deleted_at);
    }

    public function feedback(User $user, Clip $clip): bool
    {
        return $user->can(Permission::CanSubmitClipFeedback);
    }

    public function flagAny(User $user, ?Broadcaster $broadcaster = null): bool
    {
        if ($user->id === $broadcaster?->id) {
            return true;
        }

        return $user->can(Permission::CanFlagClips);
    }

    public function unflagAny(User $user, ?Broadcaster $broadcaster = null): bool
    {
        if ($user->id === $broadcaster?->id) {
            return true;
        }

        return $user->can(Permission::CanUnflagClips);
    }

    public function flag(User $user, Clip $clip): bool
    {
        if ($user->id === $clip->broadcaster_id) {
            return true;
        }

        return $user->can(Permission::CanFlagClips);
    }

    public function unflag(User $user, Clip $clip): bool
    {
        if ($user->id === $clip->broadcaster_id) {
            return true;
        }

        return $user->can(Permission::CanUnflagClips);
    }
}
