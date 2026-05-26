<?php

declare(strict_types=1);

namespace App\Policies\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Permission;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use App\Services\Twitch\TwitchService;

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

    public function dashboardAccess(User $user, Broadcaster $broadcaster, ?BroadcasterPermission $permission = null)
    {
        if ($broadcaster->id === $user->id) {
            return true;
        }

        if (! $permission instanceof BroadcasterPermission) {
            return false;
        }

        $hasAccessAsTeamMember = $broadcaster
            ->members()
            ->where('user_id', $user->id)
            ->whereHasPermission($permission)
            ->exists();

        if ($hasAccessAsTeamMember) {
            return true;
        }

        if ($broadcaster->twitch_mod_permissions?->doesntContain($permission)) {
            return false;
        }

        $twitchService = app(TwitchService::class);

        return $twitchService->asSessionUser()->isModeratorFor($broadcaster);
    }
}
