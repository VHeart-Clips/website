<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\TwitchAvatarCast;
use App\Enums\ExternalContentProxyType;
use App\Models\Contracts\ExternalProxyable;
use App\Models\Contracts\HasFilamentInfolistEntry;
use App\Models\Contracts\HasFilamentTableColumn;
use App\Models\Traits\Auditable;
use App\Models\Traits\HasExternalProxy;
use App\Models\Traits\Reportable;
use App\Models\Traits\User\UserFilamentConfiguration;
use App\Models\Traits\User\UserPermissions;
use App\Models\Traits\User\UserRelationships;
use App\Policies\UserPolicy;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Vite;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\HasComments;

/**
 * @property int $id
 */
#[UsePolicy(UserPolicy::class)]
#[WithoutIncrementing]
#[Hidden([
    'password',
    'app_authentication_secret',
    'app_authentication_recovery_codes',
    'remember_token',
    'twitch_refresh_token',
])]
class User extends Authenticatable implements Commentable, Commenter, ExternalProxyable, FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, HasDefaultTenant, HasFilamentInfolistEntry, HasFilamentTableColumn, HasName, HasTenants, MustVerifyEmail
{
    use Auditable;
    use HasComments;
    use HasExternalProxy;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use Notifiable;
    use Reportable;
    use SoftDeletes;
    use UserFilamentConfiguration;
    use UserPermissions;
    use UserRelationships;

    protected array $auditExclude = [
        'name',
        'email',
        'avatar_url',
    ];

    protected array $auditExcludeEvents = ['created'];

    protected $rememberTokenName;

    public static function getProxyUrlColumn(): string
    {
        return 'avatar_url';
    }

    public static function getProxyExtension(): string
    {
        return 'png';
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->name;
    }

    public function refresh(): self
    {
        $this->permissionCache = null;
        $this->importantRoleCache = null;
        $this->isSuperAdminCache = null;

        return parent::refresh();
    }

    public function hasVerifiedEmail(): bool
    {
        if (is_null($this->email)) {
            // since emails are optional we have to classify null as verified
            return true;
        }

        return parent::hasVerifiedEmail();
    }

    public function getPasswordAttribute(): ?string
    {
        return null;
    }

    public function proxiedContentUrl(?int $width = null, ?int $height = null): ?string
    {
        if (! $this->exists || $this->id === 0 || ! $this->getAttribute(static::getProxyUrlColumn())) {
            return Vite::asset('resources/images/png/mani.png');
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
            'avatar_url' => TwitchAvatarCast::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'twitch_refresh_token' => 'encrypted',
        ];
    }
}
