<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Clips\ClipStatus;
use App\Enums\FeatureFlag;
use App\Models\Clip;
use App\Models\Contracts\HasFilamentInfolistEntry;
use App\Models\Contracts\HasFilamentTableColumn;
use App\Models\Traits\Auditable;
use App\Models\Traits\Reportable;
use App\Models\User;
use App\Policies\Broadcaster\BroadcasterPolicy;
use App\Support\FeatureFlag\Feature;
use Database\Factories\Broadcaster\BroadcasterFactory;
use Filament\Models\Contracts\HasAvatar;
use Filament\Schemas\Components\Component as FilamentSchemaComponent;
use Filament\Tables\Columns\Column as FilamentTableColumn;
use Filament\Tables\Columns\Layout\Component as FilamentTableComponent;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[UsePolicy(BroadcasterPolicy::class)]
#[WithoutIncrementing]
class Broadcaster extends Model implements HasAvatar, HasFilamentInfolistEntry, HasFilamentTableColumn
{
    use Auditable;

    /** @use HasFactory<BroadcasterFactory> */
    use HasFactory;

    use Reportable;
    use SoftDeletes;

    public static function getFilamentInfolistEntry(string $name): FilamentSchemaComponent
    {
        return User::getFilamentInfolistEntry($name);
    }

    public static function getFilamentTableColumn(string $name): FilamentTableComponent|FilamentTableColumn
    {
        return User::getFilamentTableColumn($name);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    /**
     * @return HasMany<Clip, $this>
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class, 'broadcaster_id', 'id');
    }

    /**
     * @return HasMany<BroadcasterTeamMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(BroadcasterTeamMember::class);
    }

    /**
     * @return HasMany<BroadcasterSubmissionFilter, $this>
     */
    public function filters(): HasMany
    {
        return $this->hasMany(BroadcasterSubmissionFilter::class);
    }

    /**
     * @return HasMany<BroadcasterConsentLog, $this>
     */
    public function consentLogs(): HasMany
    {
        return $this->hasMany(BroadcasterConsentLog::class);
    }

    /**
     * @return HasOne<BroadcasterConsentLog, $this>
     */
    public function latestConsentLog(): HasOne
    {
        return $this->hasOne(BroadcasterConsentLog::class)->latestOfMany('changed_at');
    }

    public function proxiedContentUrl(): mixed
    {
        return $this->loadMissing('user')->user->proxiedContentUrl();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->proxiedContentUrl();
    }

    protected static function booted(): void
    {
        // We have to manually sort them as json will preserve order of simple arrays rip
        // technically it doesnt matter but doesnt hurt either
        static::saving(static function (Broadcaster $broadcaster): void {
            if ($broadcaster->consent) {
                $broadcaster->consent = $broadcaster->consent
                    ->sortBy(fn (BroadcasterConsent $enum) => $enum->value)
                    ->values();
            }

            if ($broadcaster->twitch_mod_permissions) {
                $broadcaster->twitch_mod_permissions = $broadcaster->twitch_mod_permissions
                    ->sortBy(fn (BroadcasterPermission $enum) => $enum->value)
                    ->values();
            }
        });

        static::updating(static function (self $broadcaster): void {
            if (! $broadcaster->isDirty('consent') || $broadcaster->id !== auth()->id()) {
                return;
            }

            BroadcasterConsentLog::create([
                'broadcaster_id' => $broadcaster->id,
                'state' => $broadcaster->consent,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        });
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->loadMissing('user')->user->name,
        );
    }

    protected function casts(): array
    {
        return [
            'consent' => AsEnumCollection::of(BroadcasterConsent::class),
            'twitch_mod_permissions' => AsEnumCollection::of(BroadcasterPermission::class),
            'submit_user_allowed' => 'boolean',
            'submit_mods_allowed' => 'boolean',
            'submit_vip_allowed' => 'boolean',
            'onboarded_at' => 'datetime',
            'default_clip_status' => ClipStatus::class,
        ];
    }

    #[Scope]
    protected function whereOnboarded(Builder $query): Builder
    {
        return $query->whereNotNull('onboarded_at');
    }

    #[Scope]
    protected function whereGaveNoConsent(Builder $query): Builder
    {
        if (Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(fn (Builder $query) => $query
            ->whereJsonLength('consent', '=', '0')
            ->orWhereNull('consent'));
    }

    /**
     * check if the broadcaster has given the consents or when no consents provided check if any consent is given
     */
    #[Scope]
    protected function whereGaveConsent(Builder $query, BroadcasterConsent|Collection|array|null $consents = null): Builder
    {
        if (Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)) {
            return $query;
        }

        if (! $consents) {
            return $query->whereJsonLength('consent', '>', '0');
        }

        if ($consents instanceof BroadcasterConsent) {
            $consents = [$consents];
        }

        return $query->whereJsonContains('consent', $consents);

    }
}
