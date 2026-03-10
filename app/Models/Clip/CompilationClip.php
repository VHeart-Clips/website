<?php

declare(strict_types=1);

namespace App\Models\Clip;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

// Pivot models are only ever used for Model to Model relationship extensions
// e.g. for casts on a pivot or for adding relationships that are stored on the pivot
// basically stuff that does not belong to any of the "actual" models as they may require
// pivot data, this is usually accessible via $model->pivot->stuff()
class CompilationClip extends Pivot
{
    use HasFactory;

    public $incrementing = true;

    /**
     * Get the list of columns for this pivot
     *
     * @return string[]
     */
    public static function getPivotColumns(): array
    {
        return [
            'id',
            'added_by',
            'added_at',
            'claimed_by',
            'claim_status',
            'claimed_at',
            'removed_at',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    protected function casts(): array
    {
        return [
            'claim_status' => CompilationClipClaimStatus::class,
            'added_at' => 'datetime',
            'claimed_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }
}
