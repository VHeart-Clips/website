<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Broadcaster\BroadcasterPermission;
use Database\Factories\Broadcaster\BroadcasterFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Broadcaster extends Model
{
    /** @use HasFactory<BroadcasterFactory> */
    use HasFactory;

    use SoftDeletes;

    public $incrementing = false;

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
    }

    protected function casts(): array
    {
        return [
            'consent' => AsEnumCollection::of(BroadcasterConsent::class),
            'twitch_mod_permissions' => AsEnumCollection::of(BroadcasterPermission::class),
            'submit_user_allowed' => 'boolean',
            'submit_mods_allowed' => 'boolean',
            'submit_vip_allowed' => 'boolean',
        ];
    }
}
