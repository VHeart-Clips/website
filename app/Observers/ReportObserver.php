<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Discord\Reports\DeleteReportWebhookJob;
use App\Jobs\Discord\Reports\ReportWebhookJob;
use App\Models\Report;

class ReportObserver
{
    public function created(Report $report): void
    {
        // No message id yet, so we just forcefully create one
        ReportWebhookJob::dispatch($report);
    }

    public function updated(Report $report): void
    {
        $this->dispatchReportDiscordWebhook($report);
    }

    public function deleted(Report $report): void
    {
        $this->dispatchReportDiscordWebhook($report);
    }

    public function restored(Report $report): void
    {
        // May not have a message id anymore (if removed by scheduler), recreate or update
        ReportWebhookJob::dispatch($report);
    }

    public function forceDeleted(Report $report): void
    {
        if ($report->discord_message_id) {
            DeleteReportWebhookJob::dispatch($report->discord_message_id, $report);
        }
    }

    // Only update if we actually changed something important (and we still got the message id)
    private function dispatchReportDiscordWebhook(Report $report): void
    {
        $changes = array_keys($report->getChanges());
        $changesWeCareAbout = array_diff($changes, ['discord_message_id', 'updated_at']);

        if ($changesWeCareAbout === [] || $report->discord_message_id === null) {
            return;
        }

        ReportWebhookJob::dispatch($report);
    }
}
