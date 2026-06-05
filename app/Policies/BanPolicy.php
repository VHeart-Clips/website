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
        // we should allow moderation with ban permission to fix typos and stuff in their own bans
        // assuming they still have ban permission overall for that bannable
        // but we also limit this to 24 hours after creating the ban to limit abuse in the future
        if (
            $ban->admin_id === $user->id
            && $user->canAny(['ban', 'unban'], $ban->bannable)
            && $ban->created_at->addDay()->isNowOrFuture()
        ) {
            return true;
        }

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
