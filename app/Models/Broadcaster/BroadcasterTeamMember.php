<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Enums\Broadcaster\BroadcasterPermission;
use App\Models\User;
use Database\Factories\Broadcaster\BroadcasterTeamMemberFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BroadcasterTeamMember extends Model
{
    /** @use HasFactory<BroadcasterTeamMemberFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return BelongsTo<Broadcaster, $this>
     */
    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(Broadcaster::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        // We have to manually sort them as json will preserve order of simple arrays rip
        // technically it doesnt matter but doesnt hurt either
        static::saving(static function (BroadcasterTeamMember $broadcasterTeamMember): void {
            if ($broadcasterTeamMember->permissions) {
                $broadcasterTeamMember->permissions = $broadcasterTeamMember->permissions
                    ->sortBy(fn (BroadcasterPermission $enum) => $enum->value)
                    ->values();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'permissions' => AsEnumCollection::of(BroadcasterPermission::class),
        ];
    }
}
