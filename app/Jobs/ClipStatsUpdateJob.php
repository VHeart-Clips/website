<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ClipVoteType;
use App\Models\Clip;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

#[DebounceFor(10, 300)]
class ClipStatsUpdateJob implements ShouldQueueAfterCommit
{
    use Batchable;
    use Queueable;

    public function __construct(public int $clipId) {}

    public function debounceId(): string
    {
        return 'ClipStatsUpdateJob:'.$this->clipId;
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->debounceId())
                ->releaseAfter(15)
                ->expireAfter(150),
        ];
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $clipId = $this->clipId;

        if (Clip::query()->whereId($clipId)->doesntExist()) {
            return;
        }

        DB::transaction(function () use ($clipId): void {
            $clip = Clip::query()->sharedLock()->find($clipId);

            if (! $clip instanceof Clip) {
                return;
            }

            $juryWeight = (int) config('vheart.clips.scoring.jury_weight', 10);
            $publicWeight = (int) config('vheart.clips.scoring.public_weight', 1);
            $impressionRatioExponent = (float) config('vheart.clips.scoring.impression_ratio_exponent', 1);

            $totalImpressions = $clip->votes()->count();

            if ($totalImpressions > 0) {

                $totalVotes = $clip->votes()->where('voted', true)->count();
                $totalJuryVotes = $clip->votes()->where('voted', true)->where('type', ClipVoteType::Jury)->count();
                $totalPublicVotes = $clip->votes()->where('voted', true)->where('type', ClipVoteType::Public)->count();

                $impressionRatio = ($totalVotes / $totalImpressions) ** $impressionRatioExponent;

                $juryVoteScore = $totalJuryVotes * $juryWeight;
                $publicVoteScore = $totalPublicVotes * $publicWeight;

                $score = round($impressionRatio * ($juryVoteScore + $publicVoteScore));
                $score = max($score, 0);
            }

            $clip->update([
                'total_votes' => $totalVotes ?? 0,
                'total_public_votes' => $totalPublicVotes ?? 0,
                'total_jury_votes' => $totalJuryVotes ?? 0,
                'total_impressions' => $totalImpressions ?? 0,
                'score' => $score ?? 0,
            ]);
        });
    }
}
