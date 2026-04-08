<?php

declare(strict_types=1);

namespace App\Models\Traits\Clip\Scopes;

use App\Enums\Clips\CompilationStatus;
use App\Models\Clip;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Clip
 */
trait ClipArchiveScopes
{
    #[Scope]
    protected function whereArchived(Builder $query): Builder
    {
        return $query->whereNotNull(['final_jury_votes', 'final_public_votes', 'final_score']);
    }

    #[Scope]
    protected function whereNotArchived(Builder $query): Builder
    {
        return $query->whereNull(['final_jury_votes', 'final_public_votes', 'final_score']);
    }

    /**
     * Get Clips that should be archived
     *
     * We will allow a 1 week buffer before we permanently archive clips though, just in case something changes
     */
    #[Scope]
    protected function whereEligibleForArchival(Builder $query): Builder
    {
        /** @var CarbonInterval $maxAge */
        $maxAge = config('vheart.clips.voting.maximum_age');

        return $query
            ->whereNotArchived()
            ->where(function (Builder $query) use ($maxAge): void {
                $query
                    ->where('created_at', '<', now()->sub($maxAge)->subWeek())
                    ->orWhereHas('compilations',
                        fn (Builder $q) => $q
                            ->whereIn('compilations.status', CompilationStatus::getVoteDisabledCases())
                            ->where('compilations.updated_at', '<', now()->subWeek())
                    );
            });
    }
}
