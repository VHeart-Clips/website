<?php

declare(strict_types=1);

namespace App\Enums\Eloquent;

use App\Enums\Filament\LucideIcon;
use App\Enums\Traits\HasHeadlineLabel;
use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum EqualityOperator: string implements HasIcon, HasLabel
{
    use HasHeadlineLabel;

    case Is = '=';
    case IsNot = '!=';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Is => LucideIcon::Equal,
            self::IsNot => LucideIcon::EqualNot,
        };
    }
}
