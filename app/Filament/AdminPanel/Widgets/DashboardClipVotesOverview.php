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
        $totalWithVotes = Clip::query()->has('votes')->count();
        $totalWithoutVotes = Clip::query()->doesntHave('votes')->whereEligibleForVoting()->count();
        $totalArchived = Clip::query()->whereArchived()->count();

        return [
            Stat::make('Eligible Clips', number_format($totalEligible))
                ->description('Total clips available for voting')
                ->icon(LucideIcon::Video),

            Stat::make('Clips with Votes', number_format($totalWithVotes))
                ->description('Eligible clips that have received votes')
                ->icon(LucideIcon::ThumbsUp),

            Stat::make('Clips without Votes', number_format($totalWithoutVotes))
                ->description('Eligible clips still awaiting votes')
                ->icon(LucideIcon::CircleDashed),

            Stat::make('Archived Clips', number_format($totalArchived))
                ->description('Total clips not in the pool anymore')
                ->icon(LucideIcon::Archive),
        ];
    }
}
