<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Reports\ReportStatus;
use App\Jobs\Discord\Reports\DeleteReportWebhookJob;
use App\Models\Report;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reports:prune-webhooks {--days=7 : Days to wait for resolved messages to be pruned} {--dry-run : Preview without making changes}')]
#[Description('Prunes report webhook messages after some time')]
class PruneReportWebhookMessagesCommand extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $count = 0;

        Report::query()
            ->withTrashed()
            ->whereNotNull('discord_message_id')
            ->where('status', ReportStatus::Resolved)
            ->where('updated_at', '<=', now()->subDays($days))
            ->lazy()
            ->each(function (Report $report) use (&$count, $dryRun): void {
                if (! $dryRun) {
                    DeleteReportWebhookJob::dispatch($report->discord_message_id, $report);
                }

                $count++;
            });

        $this->info(($dryRun ? 'Would prune' : 'Scheduled deletion for')." $count resolved report webhook message(s) older than $days days.");

        return self::SUCCESS;
    }
}
