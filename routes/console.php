<?php

declare(strict_types=1);

use App\Console\Commands\ArchiveClipVotesCommand;
use App\Console\Commands\CreateWeeklyEpisodesCommand;
use App\Console\Commands\PruneReportWebhookMessagesCommand;
use App\Console\Commands\RefreshClipsCommand;
use App\Console\Commands\ReleaseStaleReportsCommand;
use App\Console\Commands\SyncTopGamesCommand;
use App\Enums\FeatureFlag;
use App\Models\Audit;
use App\Support\FeatureFlag\Feature;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Support\Facades\Schedule;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Backup\Commands\MonitorCommand;

Schedule::call(static function () {
    Audit::query()
        ->where('created_at', '<=', now()->subDays(30))
        ->where(function ($query) {
            $query->whereNotNull('ip_address')
                ->orWhereNotNull('user_agent');
        })
        ->update([
            'ip_address' => null,
            'user_agent' => null,
        ]);
})
    ->name('Minify Audit Logs')
    ->description('Deletes IP and User Agent data from Audit logs that are older than 30 days.')
    ->onOneServer()
    ->hourly();

Schedule::command(PruneCommand::class)
    ->name('Prune Models')
    ->description('Prune Models based on their Prune definition')
    ->onOneServer()
    ->daily();

Schedule::call(static function (): void {
    $disk = Storage::disk('exports');

    collect($disk->files())
        ->filter(fn (string $file): bool => str_ends_with($file, '.zip') && $disk->lastModified($file) < now()->addMinutes(5)->getTimestamp())
        ->each(fn (string $file): bool => $disk->delete($file));
})
    ->name('Clean Data Exports')
    ->description('Cleans orphaned data exports older than one hour on the exports disk.')
    ->onOneServer()
    ->everyFifteenMinutes();

Schedule::command(ArchiveClipVotesCommand::class)
    ->skip(fn () => ! Feature::isActive(FeatureFlag::ArchiveClipsSchedule))
    ->runInBackground()
    ->onOneServer()
    ->daily();

Schedule::command(BackupCommand::class)->runInBackground()->onOneServer()->hourly();
Schedule::command(CleanupCommand::class)->runInBackground()->onOneServer()->daily()->at('00:15');
Schedule::command(MonitorCommand::class)->runInBackground()->onOneServer()->dailyAt('00:30');

Schedule::command(CreateWeeklyEpisodesCommand::class)->onOneServer()->dailyAt('06:00');
Schedule::command(SyncTopGamesCommand::class)->onOneServer()->everyFourHours();
Schedule::command(ReleaseStaleReportsCommand::class)->onOneServer()->hourly();
Schedule::command(RefreshClipsCommand::class)->runInBackground()->onOneServer()->everyFiveMinutes();
Schedule::command(PruneReportWebhookMessagesCommand::class)->runInBackground()->onOneServer()->everyFifteenMinutes();
