<?php

declare(strict_types=1);

/*
 * Even though we will never store this in the database (as multiple devices may have different settings)
 * this will stay in UserSettings as this is technically still something the user can configure lol
 */

namespace App\Enums\UserSettings;

use App\Enums\Filament\LucideIcon;
use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Basic enum for Theme state related things
 */
enum ThemePreference: string implements HasIcon, HasLabel
{
    use HasTranslatedLabel;

    case Light = 'light';
    case Dark = 'dark';
    case System = 'system';

    public function getIcon(): LucideIcon
    {
        return match ($this) {
            self::Light => LucideIcon::Sun,
            self::Dark => LucideIcon::Moon,
            self::System => LucideIcon::Monitor,
        };
    }
}
