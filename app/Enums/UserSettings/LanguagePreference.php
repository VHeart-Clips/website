<?php

declare(strict_types=1);

namespace App\Enums\UserSettings;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum LanguagePreference: string implements HasIcon, HasLabel
{
    case German = 'de';
    case English = 'en';

    // we do not translate this as the user should be able to read their own language name imo
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::German => 'Deutsch',
            self::English => 'English',
        };
    }

    /**
     * @return string ISO 3166-1 Alpha-2 Country code
     */
    public function getFlagCode(): string
    {
        // override country flags here in case flag-country doesnt have it
        return match ($this) {
            self::English => 'gb',
            default => $this->value,
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return 'flag-country-'.$this->getFlagCode();
    }
}
