<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ExternalContentProxyType;
use App\Enums\Permission;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Policies\UserPolicy;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\HasComments;

// We tell laravel where to find the policy class
// While the name convention should allow auto-detection, we want to stay explicit to make it clear.
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements Commentable, Commenter, ExternalProxyable, FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, MustVerifyEmail
{
    use HasComments;
    use HasExternalProxy;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use Notifiable;
    use Reportable;
    use SoftDeletes;

    public $incrementing = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
        'remember_token',
        'twitch_refresh_token',
    ];

    protected $rememberTokenName;

    protected ?Role $importantRoleCache = null;

    /** @var array<int,Permission>|null */
    protected ?array $permissionCache = null;

    public static function getProxyUrlColumn(): string
    {
        return 'avatar_url';
    }

    public static function getProxyExtension(): string
    {
        return 'png';
    }

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

    public function getAppAuthenticationHolderName(): string
    {
        return $this->name;
    }

    /**
     * Assign a single Role to the user
     */
    public function assignRole(int|string|Role $role): void
    {
        $this->roles()->attach($role);
        $this->permissionCache = null;
        $this->importantRoleCache = null;
    }

    /**
     * @return BelongsToMany<Role, $this, Pivot>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
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
    }

    public function refresh(): self
    {
        $this->permissionCache = null;
        $this->importantRoleCache = null;

        return parent::refresh();
    }

    /*
     * Hook into some relationship logic to clear our cache
     */
    public function setRelation($relation, $value): self
    {
        if ($relation === 'roles') {
            $this->permissionCache = null;
            $this->importantRoleCache = null;
        }

        return parent::setRelation($relation, $value);
    }

    /**
     * @return HasMany<BroadcasterFilter, $this>
     */
    public function broadcasterFilter(): HasMany
    {
        return $this->hasMany(BroadcasterFilter::class, 'broadcaster_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function broadcastedClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'broadcaster_id');
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function createdClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'creator_id');
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function submittedClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'submitter_id');
    }

    public function hasVerifiedEmail(): bool
    {
        if (is_null($this->email)) {
            // since emails are optional we have to classify null as verified
            return true;
        }

        return parent::hasVerifiedEmail();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAny([
            Permission::ViewAnyFaqEntry,
            Permission::ViewAnyClip,
            Permission::ViewAnyRole,
            Permission::ViewAnyUser,
            Permission::ViewAnyCategory,
            Permission::ViewAnyReport,
            Permission::ViewAnyCompilation,
        ]);
    }

    /**
     * Tell Filament we dont have a password
     */
    public function getPasswordAttribute(): ?string
    {
        return null;
    }

    public function proxiedContentUrl(?int $width = null, ?int $height = null): ?string
    {
        if (! $this->exists || $this->id === 0) {
            return Vite::asset('resources/images/png/cat.png');
        }

        return $this->generateExternalProxyUrl($width, $height);
    }

    public function getProxyType(): ExternalContentProxyType
    {
        return ExternalContentProxyType::TwitchUser;
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
            'clip_permission' => 'boolean',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'twitch_refresh_token' => 'encrypted',
            'rules' => 'array',
        ];
    }
}
