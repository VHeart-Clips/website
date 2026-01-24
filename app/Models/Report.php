<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Reports\ReportReason;
use App\Enums\Reports\ReportStatus;
use App\Policies\ReportPolicy;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(ReportPolicy::class)]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, SoftDeletes;

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    #[Scope]
    protected function unclaimed(Builder $query): void
    {
        $query->whereNull('claimed_by');
    }

    #[Scope]
    protected function claimed(Builder $query): void
    {
        $query->whereNotNull('claimed_by');
    }

    #[Scope]
    protected function claimedBy(Builder $query, User|int $user): void
    {
        $query->where('claimed_by', $user instanceof User ? $user->id : $user);
    }

    #[Scope]
    protected function claimedByMe(Builder $query): void
    {
        $query->claimedBy(auth()->user());
    }

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'claimed_at' => 'datetime',
            'status' => ReportStatus::class,
            'reason' => ReportReason::class,
        ];
    }
}
