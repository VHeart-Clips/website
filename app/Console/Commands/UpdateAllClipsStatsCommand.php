<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ClipStatsUpdateJob;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\Scopes\ClipWithoutBannedCategoryScope;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('app:update-all-clips-stats {--dry-run} {--sync}')]
#[Description('Dispatch ClipStatsUpdateJob for all Clips')]
class UpdateAllClipsStatsCommand extends Command
{
    public function handle(): int
    {
        $query = Clip::query()
            ->withoutGlobalScopes([
                ClipPermissionScope::class,
                ClipWithoutBannedCategoryScope::class
            ]);

        $total = $query->clone()->count('id');

        if ($total === 0) {
            $this->info('No clips to start Job for');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Would start ClipStatsUpdateJob for {$total} clip(s).");

            return self::SUCCESS;
        }

        $isSync = (bool) $this->option('sync');

        if ($isSync) {
            $this->info('Running Sync');

            $this->withProgressBar($query->clone()->lazyByIdDesc(100), function (Clip $clip): void {
                dispatch_sync(new ClipStatsUpdateJob($clip->id));
            });

        } else {
            $this->info('Running Async');
            DB::transaction(function () use ($query): void {
                $this->withProgressBar($query->clone()->lazyByIdDesc(100), function (Clip $clip): void {
                    dispatch(new ClipStatsUpdateJob($clip->id)->afterCommit());
                });
            });
        }


        $this->newLine();

        if ($isSync) {
            $this->info("Updated Stats for {$total} clip(s)");
        } else {
            $this->info("Startet ClipStatsUpdateJob votes for {$total} clip(s).");
        }

        return self::SUCCESS;
    }
}
