<?php

declare(strict_types=1);

namespace App\Models\Broadcaster;

use App\Enums\Broadcaster\RemovalRequestStatus;
use App\Models\Clip;
use App\Models\Traits\Auditable;
use App\Models\User;
use App\Policies\Broadcaster\RemovalRequestPolicy;
use Database\Factories\Broadcaster\RemovalRequestFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Kirschbaum\Commentions\HasComments;

#[UsePolicy(RemovalRequestPolicy::class)]
class RemovalRequest extends Model
{
    use Auditable;
    use HasComments;

    /** @use HasFactory<RemovalRequestFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Broadcaster, $this>
     */
    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(Broadcaster::class)
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return BelongsTo<Clip, $this>
     */
    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class)
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function isResolved(): bool
    {
        return $this->status->isResolved();
    }

    protected function casts(): array
    {
        return [
            'status' => RemovalRequestStatus::class,
            'claimed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }
}
