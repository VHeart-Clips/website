<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets;

use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Models\Clip;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardClipVotesOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Clip Votes';

    public static function canView(): bool
    {
        return auth()->user()->can([
            Permission::ViewAnyClip,
            Permission::ViewAnyUser,
        ]);
    }

    protected function getStats(): array
    {
        $totalEligible = Clip::query()->whereEligibleForVoting()->count();
        $totalEligibleWithVotes = Clip::query()->whereEligibleForVoting()->has('votes')->count();
        $totalWithVotes = Clip::query()->has('votes')->count();
        $totalEligibleWithoutVotes = Clip::query()->doesntHave('votes')->whereEligibleForVoting()->count();
        $totalClipsWithoutVotes = Clip::query()->doesntHave('votes')->count();
        $totalArchived = Clip::query()->whereArchived()->count();

        return [
            Stat::make('Eligible Clips', number_format($totalEligible))
                ->description('Total clips available for voting')
                ->icon(LucideIcon::Video),

            Stat::make('Eligible Clips with Votes', number_format($totalEligibleWithVotes))
                ->description('Eligible clips that have received votes')
                ->icon(LucideIcon::ThumbsUp),

            Stat::make('Total Clips with Votes', number_format($totalWithVotes))
                ->description('Total clips that have received votes')
                ->icon(LucideIcon::ThumbsUp),

            Stat::make('Eligible Clips without Votes', number_format($totalEligibleWithoutVotes))
                ->description('Eligible clips still awaiting votes')
                ->icon(LucideIcon::CircleDashed),

            Stat::make('Total Clips without Votes', number_format($totalClipsWithoutVotes))
                ->description('Total clips still awaiting votes (or never will)')
                ->icon(LucideIcon::CircleDashed),

            Stat::make('Archived Clips', number_format($totalArchived))
                ->description('Total clips not in the pool anymore')
                ->icon(LucideIcon::Archive),
        ];
    }
}
