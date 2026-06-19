<?php

declare(strict_types=1);

namespace App\Enums\Reports;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use JustinKluever\DiscordWebhookBuilder\Support\Color;

enum ReportStatus: int implements HasColor, HasLabel
{
    use HasTranslatedLabel;

    case Pending = 0;
    case InReview = 1;
    case Resolved = 2;

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'danger',
            self::InReview => 'primary',
            self::Resolved => 'success'
        };
    }

    // We could make them similar to the filament colors but i like the pink
    // the other colors where selected based on the Pending state "Triadic" on htmlcolorcodes
    public function getDiscordColor(): Color
    {
        return match ($this) {
            self::Pending => Color::fromHex('#E71D73'), // Pink
            self::InReview => Color::fromHex('#1D75E7'), // Blue
            self::Resolved => Color::fromHex('#75E71D') // Green
        };
    }

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'reports.enums';
    }
}
