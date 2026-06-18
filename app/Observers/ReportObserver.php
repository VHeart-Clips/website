<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Discord\ReportWebhookJob;
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
        // TODO: should probably delete the message "instantly" on force delete as we wont have the reference in the future
        // also not possible to update the message at this point since we literally delete the model which causes the job to delete itself lol
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
