<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\BroadcasterPermission;
use App\Enums\Clips\ClipStatus;
use App\Enums\Eloquent\SetOperator;
use App\Enums\FeatureFlag;
use App\Models\Clip;
use App\Models\Contracts\HasFilamentInfolistEntry;
use App\Models\Contracts\HasFilamentTableColumn;
use App\Models\Traits\Auditable;
use App\Models\Traits\Bannable;
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
use Illuminate\Support\Facades\DB;
use JsonException;

#[UsePolicy(BroadcasterPolicy::class)]
#[WithoutIncrementing]
class Broadcaster extends Model implements HasAvatar, HasFilamentInfolistEntry, HasFilamentTableColumn
{
    use Auditable;
    use Bannable;

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
     * Without this it is possible to trigger a 500 error from the dashboard with non-numeric tenant ids
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (! is_numeric($value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
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

    public function proxiedContentUrl(): ?string
    {
        return $this->loadMissing('user')->user?->avatar_url;
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

        return $query->whereHasGivenConsent([], SetOperator::Exact);
    }

    /**
     * Will scope based on the Broadcaster consent column.
     *
     * This scope will include any Broadcaster with any consent if no input has been provided
     *
     * #### SetOperator Parameter:
     * - {@see SetOperator::Any} Will include Broadcasters that have **any** of the input Consents
     * - {@see SetOperator::AnyMissing} Will only include Broadcasters that does not have **all** of the input Consents
     * - {@see SetOperator::All} Will only include Broadcasters that have **all** of the input Consents
     * - {@see SetOperator::None} Will only include Broadcasters that have **none** of the input Consents
     * - {@see SetOperator::Exact} Will only include Broadcasters who **exactly** have the input Consents
     *
     * @param  BroadcasterConsent|Collection<int, BroadcasterConsent>|array<BroadcasterConsent>|null  $consents
     *
     * @throws JsonException
     */
    #[Scope]
    protected function whereHasGivenConsent(
        Builder $query,
        BroadcasterConsent|Collection|array|null $consents = null,
        SetOperator $operator = SetOperator::Exact,
    ): Builder {
        if (Feature::isActive(FeatureFlag::IgnoreBroadcasterConsent)) {
            return $query;
        }

        if ($consents === null) {
            return $query->whereJsonLength('consent', '>', '0');
        }

        $values = collect($consents)
            ->filter()
            ->map(fn (BroadcasterConsent $consent) => $consent->value);
        $valueCount = $values->count();

        return match ($operator) {
            SetOperator::Any => $query
                ->where(fn (Builder $subQuery): Builder => $values->reduce(
                    fn (Builder $subSubQuery, $value) => $subSubQuery->orWhereJsonContains('consent', $value),
                    $subQuery
                )),

            SetOperator::AnyMissing => $query
                ->whereJsonLength('consent', '<', count(BroadcasterConsent::cases()))
                ->where(fn (Builder $subQuery): Builder => $subQuery
                    ->whereJsonContains('consent', $values, 'or')
                    ->whereJsonDoesntContain('consent', $values, 'or')
                ),

            SetOperator::All => $query->whereJsonContains('consent', $values),

            SetOperator::None => $query->where(fn (Builder $subQuery): Builder => $values->reduce(
                fn (Builder $subSubQuery, $value) => $subSubQuery->whereJsonDoesntContain('consent', $value),
                $subQuery
            )),

            SetOperator::Exact => $query
                ->where(fn (Builder $subQuery): Builder => $subQuery
                    ->whereJsonLength('consent', '=', $valueCount)
                    ->when($values->isEmpty(), fn (Builder $whenEmptyQuery): Builder => $whenEmptyQuery
                        ->orWhereNull('consent'))
                    ->when($values->isNotEmpty(), fn (Builder $whenNotEmptyQuery): Builder => $whenNotEmptyQuery
                        ->whereJsonContains('consent', $values))
                ),
        };
    }

    #[Scope]
    protected function whereGaveTwitchModPermission(Builder $query, BroadcasterPermission|Collection|array|null $permissions = null)
    {
        if (! Feature::isActive(FeatureFlag::BroadcasterTenant)) {
            return $query->whereRaw(DB::raw('1 = 0'));
        }

        if (! $permissions) {
            return $query->whereJsonLength('twitch_mod_permissions', '>', '0');
        }

        if ($permissions instanceof BroadcasterPermission) {
            $permissions = [$permissions];
        }

        return $query->whereJsonContains('twitch_mod_permissions', $permissions);
    }
}
