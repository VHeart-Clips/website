<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ShortUrl;
use App\Models\User;

class ShortUrlPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewAnyShortUrl);
    }

    public function view(User $user, ShortUrl $shortUrl): bool
    {
        return $user->can(Permission::ViewAnyShortUrl);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::UpdateAnyShortUrl);
    }

    public function update(User $user, ShortUrl $shortUrl): bool
    {
        return $user->can(Permission::UpdateAnyShortUrl);
    }

    public function delete(User $user, ShortUrl $shortUrl): bool
    {
        return $user->can(Permission::DeleteAnyShortUrl);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::DeleteAnyShortUrl);
    }

    public function restore(User $user, ShortUrl $shortUrl): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, ShortUrl $shortUrl): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
