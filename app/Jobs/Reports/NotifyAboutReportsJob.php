<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Jobs\Discord\ReportWebhookJob;
use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Support\Facades\Cache;

/**
 * Notify moderation team about new reports
 * because reports may be handled automatically we debounce this to
 * only notify about reports that actually need attention to make it less annoying
 */
#[DebounceFor(60)]
class NotifyAboutReportsJob implements ShouldQueue
{
    use Queueable;

    public const string NOTIFICATION_CACHE_KEY_PREFIX = 'notifications:reports:';

    public function handle(): void
    {
        $openReports = Report::query()
            ->unclaimed()
            ->orderBy('id', 'asc');

        $openReports->eachById(function (Report $report): void {
            if (Cache::has(self::NOTIFICATION_CACHE_KEY_PREFIX.$report->id)) {
                return;
            }

            Cache::put(self::NOTIFICATION_CACHE_KEY_PREFIX.$report->id, true, now()->addWeek());

            ReportWebhookJob::dispatchSync($report);
        });
    }
}
