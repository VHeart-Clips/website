<?php

namespace App\Enums\Leaderboard;

use App\Enums\Traits\HasTranslatedLabel;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Filament\Support\Contracts\HasLabel;

enum LeaderboardRange: string implements HasLabel
{
    use HasTranslatedLabel;

    case ThisWeek = 'this_week';
    case LastWeek = 'last_week';
    case Month = 'month';
    case LastMonth = 'last_month';
    case Year = 'year';


    public function getTimestampFrom(): CarbonImmutable
    {
        $timestamp = match($this) {
            self::ThisWeek => now()->previous(Carbon::THURSDAY),
            self::LastWeek => now()->previous(Carbon::THURSDAY)->previous(Carbon::THURSDAY),
            self::Month => now()->subMonthNoOverflow(),
            self::LastMonth => now()->subMonthNoOverflow()->startOfMonth(),
            self::Year => now()->subYearNoOverflow(),
        };

        return $timestamp->startOfDay();
    }

    public function getTimestampTo(): CarbonImmutable
    {
        return match($this) {
            self::ThisWeek => now()->next(Carbon::THURSDAY)->startOfDay(),
            self::LastWeek => now()->previous(Carbon::THURSDAY)->startOfDay(),
            self::Month => now()->endOfDay(),
            self::LastMonth => now()->subMonthNoOverflow()->endOfMonth()->endOfDay(),
            self::Year => now()->endOfDay(),
        };
    }

    public function getCacheKey(): string
    {
        return md5($this->getTimestampFrom()->toISOString()."-".$this->getTimestampTo()->toISOString());
    }

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'leaderboard.enums';
    }
}
