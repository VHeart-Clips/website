<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Enums\Reports\ReportStatus;
use App\Models\Report;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardModerationOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Moderation';

    protected static ?int $sort = -100;

    public static function canView(): bool
    {
        return auth()->user()->can(Permission::ViewAnyReport);
    }

    protected function getStats(): array
    {
        $pendingReports = Report::where('status', ReportStatus::Pending)->count();
        $activeReports = Report::where('status', ReportStatus::InReview)->count();
        $reportsThisWeek = Report::withTrashed()->where('created_at', '>=', now()->startOfWeek())->count();

        return [
            Stat::make('Pending Reports', number_format($pendingReports))
                ->icon($pendingReports > 0 ? Heroicon::ExclamationCircle : Heroicon::CheckCircle)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
            Stat::make('Active Reports', number_format($activeReports))
                ->icon($activeReports > 0 ? Heroicon::ExclamationCircle : Heroicon::CheckCircle)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
            Stat::make('Reports This Week', number_format($reportsThisWeek))
                ->icon(Heroicon::Flag),
        ];
    }
}
