<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\VoteObserver;
use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(VoteObserver::class)]
class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    public function clip(): BelongsTo
    {
        return $this->BelongsTo(Clip::class);
    }

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
}
