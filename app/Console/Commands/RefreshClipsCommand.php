<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\UpdateClipsBatchJob;
use App\Models\Clip;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('clips:refresh')]
#[Description('Updates clip metadata from Twitch based on their next_refresh_after timestamp')]
class RefreshClipsCommand extends Command
{
    public function handle(): int
    {
        $jobCount = 0;
        $clipCount = 0;

        Clip::query()
            ->whereShouldRefresh()
            ->orderBy('next_refresh_after')
            ->select('id')
            ->chunkById(100, function ($clips) use (&$jobCount, &$clipCount): void {
                $ids = $clips->pluck('id')->all();

                DB::transaction(static function () use ($ids): void {
                    Clip::query()
                        ->whereIn('id', $ids)
                        ->update([
                            'next_refresh_after' => null,
                        ]);

                    UpdateClipsBatchJob::dispatch($ids)
                        ->afterCommit();
                });

                $jobCount++;
                $clipCount += count($ids);
            });

        $this->info("Dispatched $jobCount jobs for $clipCount clips.");

        return self::SUCCESS;
    }
}
