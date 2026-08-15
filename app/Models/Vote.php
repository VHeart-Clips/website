<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterval;
use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    #[Scope]
    protected function whereConsideredStable(Builder $query): Builder
    {
        /** @var CarbonInterval $maxAge */
        $maxAge = config('vheart.clips.voting.maximum_age');

        return $query->where('created_at', '>=', now()->sub($maxAge));
    }
}
