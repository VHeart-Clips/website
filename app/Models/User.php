<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Permission;
use App\Policies\UserPolicy;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticatable;

// We tell laravel where to find the policy class
// While the name convention should allow auto-detection, we want to stay explicit to make it clear.
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public $incrementing = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'twitch_refresh_token',
    ];

    protected $rememberTokenName = null;

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
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Sync Roles to the user
     */
    public function syncRoles(array $roles): void
    {
        $this->roles()->sync($roles);
        $this->permissionCache = null;
    }

    public function refresh(): self
    {
        $this->permissionCache = null;

        return parent::refresh();
    }

    /*
     * Hook into some relationship logic to clear our cache
     */
    public function setRelation($relation, $value): self
    {
        if ($relation === 'roles') {
            $this->permissionCache = null;
        }

        return parent::setRelation($relation, $value);
    }

    public function broadcasterUserFilter(): MorphToMany
    {
        return $this->morphedByMany(self::class, 'filter', 'broadcaster_filter', 'broadcaster_id');
    }

    public function broadcasterGameFilter(): MorphToMany
    {
        return $this->morphedByMany(Game::class, 'filter', 'broadcaster_filter', 'broadcaster_id');
    }

    public function hasVerifiedEmail(): bool
    {
        if (is_null($this->email_verified_at)) {
            // since emails are optional we have to classify null as verified
            return true;
        }

        return parent::hasVerifiedEmail();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'twitch_refresh_token' => 'encrypted',
            'rules' => 'array',
        ];
    }
}
