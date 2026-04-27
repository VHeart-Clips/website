<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Vote;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class GenerateVotingQueueAction
{
    private const int QUEUE_SIZE = 50;

    /**
     * @return list<positive-int> Clip Ids
     */
    public function execute(Authenticatable $user): array
    {
        /** @var CarbonInterval $maxAge */
        $maxAge = config('vheart.clips.voting.maximum_age');
        $boostExponent = (int) config('vheart.clips.voting.interaction_boost_exponent');

        $maxVotes = Vote::query()
            ->where('created_at', '>=', now()->sub($maxAge))
            ->groupBy('clip_id')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(1)
            ->count();

        $broadcasters = Broadcaster::query()
            ->whereHas('clips', fn (Builder $q) => $q->whereEligibleForVoting($user))
            ->inRandomOrder()
            ->limit(self::QUEUE_SIZE)
            ->with([
                'clips' => fn (HasMany $q) => $q->whereEligibleForVoting($user)
                    ->select('id', 'broadcaster_id')
                    // While still being random we boost clips with very low interactions, basically 0 = x2, Max = x1 multiplier
                    // We still use pure randomness on selecting the broadcasters though (where its important), this is just to make sure clips get a proper distribution/exposure
                    // so we can get useful data and dont have hundreds of clips with almost no interactions waiting for archival only
                    ->orderByRaw('
                        -ln(random()) * (1.0 + power(GREATEST(? - (SELECT COUNT(*) FROM votes WHERE votes.clip_id = clips.id), 0)::float / NULLIF(?, 0), ?))
                    ', [$maxVotes, $maxVotes, $boostExponent])
                    ->limit(1),
            ])
            ->get(['id']);

        return $broadcasters
            ->pluck('clips')
            ->flatten()
            ->filter()
            ->pluck('id')
            ->shuffle()
            ->toArray();
    }
}
