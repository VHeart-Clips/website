<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Models\Report;
use Exception;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Support\Facades\Cache;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

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

            $this->notifyDiscord($report);
        });
    }

    private function notifyDiscord(Report $report): void
    {
        try {
            DiscordAlert::to('moderation')->message('<@&1494691682422226996>', [[
                'title' => 'New Report ('.$report->reason->name.')',
                'url' => Filament::getPanel('admin')->getResourceUrl($report, 'view'),
                'color' => '#e71d73',
            ]]);
        } catch (Exception $exception) {
            report($exception);
        }
    }
}
