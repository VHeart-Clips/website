<?php

declare(strict_types=1);

namespace App\Models\Traits\User;

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @mixin User
 */
trait UserPermissions
{
    protected ?Role $importantRoleCache = null;

    protected ?bool $isSuperAdminCache = null;

    /** @var array<int,Permission>|null */
    protected ?array $permissionCache = null;

    /**
     * @return array<int, Permission>
     */
    public function permissions(): array
    {
        // We only want to fetch it once per instance
        // this cache will be cleared if we change anything though
        if ($this->permissionCache !== null) {
            return $this->permissionCache;
        }

        // aggregate all permissions based on our roles
        // join role_permissions with user_roles where role_id = role_id
        // where user_id = X
        // only return unique/distinct 'role_permissions.permission' values, if 2 roles have the same permission we only need it once
        $rawPermissions = DB::table('role_permissions')
            ->join('user_roles', 'role_permissions.role_id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $this->id)
            ->distinct()
            ->pluck('role_permissions.permission');

        return $this->permissionCache = $rawPermissions
            ->map(fn ($perm) => Permission::tryFrom($perm))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Assign a single Role to the user
     */
    public function assignRole(int|string|Role $role): void
    {
        $this->roles()->attach($role);
        $this->permissionCache = null;
        $this->importantRoleCache = null;
        $this->isSuperAdminCache = null;
    }

    /**
     * The role with the highest weight on this user
     */
    public function getRole(): ?Role
    {
        if ($this->importantRoleCache instanceof Role) {
            return $this->importantRoleCache;
        }

        // Use already cached state if possible
        if ($this->relationLoaded('roles')) {
            $this->importantRoleCache = $this->roles->sortByDesc('weight')->first();
        } else {
            $this->importantRoleCache = $this->roles()->orderByDesc('weight')->first();
        }

        return $this->importantRoleCache;
    }

    /**
     * Sync Roles to the user
     */
    public function syncRoles(array $roles): void
    {
        $this->roles()->sync($roles);
        $this->permissionCache = null;
        $this->importantRoleCache = null;
        $this->isSuperAdminCache = null;
    }

    /**
     * Returns true if the user is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdminCache ?? ($this->isSuperAdminCache = $this->roles()->where('id', 0)->exists());
    }
}
