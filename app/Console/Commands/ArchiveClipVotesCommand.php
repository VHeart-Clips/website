<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ArchiveClipVotesAction;
use App\Models\Clip;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('clips:archive-votes {--dry-run}')]
#[Description('Archives vote counts onto clips and prunes vote records.')]
class ArchiveClipVotesCommand extends Command
{
    public function handle(ArchiveClipVotesAction $action): int
    {
        $query = Clip::whereEligibleForArchival()
            ->withVoteCount()
            ->withScore();
        $total = $query->count();

        if ($total === 0) {
            $this->info('No clips eligible for archival.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Would archive votes for {$total} clip(s).");

            return self::SUCCESS;
        }

        $this->withProgressBar($query->lazyById(100), function (Clip $clip) use ($action): void {
            try {
                $action->execute($clip);
            } catch (Throwable $throwable) {
                report($throwable);
                $this->warn("Could not archive clip {$clip->id}: ".$throwable->getMessage());
            }
        });

        $this->newLine();
        $this->info("Archived votes for {$total} clip(s).");

        return self::SUCCESS;
    }
}
