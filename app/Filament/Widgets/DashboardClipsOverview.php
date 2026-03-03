<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ClipVoteType;
use App\Enums\Permission;
use App\Models\Clip;
use App\Models\Vote;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardClipsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Clips';

    public static function canView(): bool
    {
        return auth()->user()->can(Permission::ViewAnyClip);
    }

    protected function getStats(): array
    {
        $totalClips = Clip::count();
        $totalClipsToday = Clip::where('created_at', '>=', now()->startOfDay())->count();
        $totalClipsThisWeek = Clip::where('created_at', '>=', now()->startOfWeek())->count();
        $totalClipsThisMonth = Clip::where('created_at', '>=', now()->startOfMonth())->count();

        $averageDuration = Clip::avg('duration') ?? 0;
        $clipsLast30Days = Clip::where('created_at', '>=', now()->subDays(30))->count();
        $averageClipsPerDay = $clipsLast30Days / 30;

        $totalVotedClips = Clip::whereHas('votes')->count();

        $totalVotes = Vote::where('type', ClipVoteType::Public)
            ->where('voted', true)
            ->count();
        $averageVotesPerClip = $totalVotedClips > 0 ? ($totalVotes / $totalVotedClips) : 0;

        $totalJuryVotes = Vote::where('type', ClipVoteType::Jury)
            ->where('voted', true)
            ->count();
        $averageJuryVotesPerClip = $totalVotedClips > 0 ? ($totalJuryVotes / $totalVotedClips) : 0;

        $totalSkips = Vote::where('voted', false)->count();
        $averageSkipsPerClip = $totalVotedClips > 0 ? ($totalSkips / $totalVotedClips) : 0;

        return [
            Stat::make('Total Clips Submitted', number_format($totalClips))
                ->icon(Heroicon::VideoCamera),
            Stat::make('Clips Submitted Today', number_format($totalClipsToday))
                ->icon(Heroicon::VideoCamera),
            Stat::make('Clips Submitted This Week', number_format($totalClipsThisWeek))
                ->icon(Heroicon::VideoCamera),
            Stat::make('Clips Submitted This Month', number_format($totalClipsThisMonth))
                ->icon(Heroicon::VideoCamera),

            Stat::make('Avg. Clip Duration', number_format($averageDuration).'s')
                ->icon(Heroicon::Clock),
            Stat::make('Avg. Daily Submissions (30 Days)', number_format($averageClipsPerDay))
                ->icon(Heroicon::ChartBar),

            Stat::make('Average Public Votes per Clip', number_format($averageVotesPerClip))
                ->icon(Heroicon::HandThumbUp),
            Stat::make('Average Jury Votes per Clip', number_format($averageJuryVotesPerClip))
                ->icon(Heroicon::Star),
            Stat::make('Average Skips per Clip', number_format($averageSkipsPerClip))
                ->icon(Heroicon::NoSymbol),
        ];
    }
}
