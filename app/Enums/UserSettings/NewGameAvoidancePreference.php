<?php

declare(strict_types=1);

namespace App\Enums\UserSettings;

use App\Enums\Traits\HasTranslatedDescription;
use App\Enums\Traits\HasTranslatedLabel;
use Carbon\CarbonImmutable;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * @todo currently just a placeholder, requires properly translated labels and descriptions when implemented
 */
enum NewGameAvoidancePreference: string implements HasDescription, HasLabel
{
    use HasTranslatedDescription;
    use HasTranslatedLabel;

    case Week = 'week';
    case Month = 'month';
    case QuarterYear = 'quarter_year';
    case HalfYear = 'half_year';
    case Year = 'year';

    public function getCutOff(): CarbonImmutable
    {
        return match ($this) {
            self::Week => CarbonImmutable::now()->subWeek(),
            self::Month => CarbonImmutable::now()->subMonth(),
            self::QuarterYear => CarbonImmutable::now()->subMonths(3),
            self::HalfYear => CarbonImmutable::now()->subMonths(6),
            self::Year => CarbonImmutable::now()->subYear(),
        };
    }
}
