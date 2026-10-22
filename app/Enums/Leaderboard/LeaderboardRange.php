<?php

declare(strict_types=1);

namespace App\Enums\Leaderboard;

use App\Enums\Traits\HasTranslatedLabel;
use Carbon\CarbonImmutable;
use Carbon\Constants\UnitValue;
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
        $now = CarbonImmutable::now();

        return match ($this) {
            self::ThisWeek => $now->startOfWeek(UnitValue::THURSDAY),
            self::LastWeek => $now->subWeek()->startOfWeek(UnitValue::THURSDAY),
            self::Month => $now->subMonthNoOverflow()->startOfDay(),
            self::LastMonth => $now->subMonthNoOverflow()->startOfMonth(),
            self::Year => $now->subYearNoOverflow()->startOfDay(),
        };
    }

    public function getTimestampTo(): CarbonImmutable
    {
        $now = CarbonImmutable::now();

        return match ($this) {
            self::ThisWeek => $now->endOfWeek(UnitValue::WEDNESDAY),
            self::LastWeek => $now->subWeek()->endOfWeek(UnitValue::WEDNESDAY),
            self::LastMonth => $now->subMonthNoOverflow()->endOfMonth(),
            default => $now->endOfDay(),
        };
    }

    public function getCacheKey(): string
    {
        return md5($this->getTimestampFrom()->toISOString().'-'.$this->getTimestampTo()->toISOString());
    }

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'leaderboard.enums';
    }
}
