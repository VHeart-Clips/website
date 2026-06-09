<?php

declare(strict_types=1);

namespace App\Enums\Eloquent;

use App\Enums\Filament\LucideIcon;
use App\Enums\Traits\HasHeadlineLabel;
use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ComparisonOperator: string implements HasIcon, HasLabel
{
    use HasHeadlineLabel;

    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case Equals = '=';
    case NotEquals = '!=';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::GreaterThan => LucideIcon::ChevronRight,
            self::GreaterThanOrEqual => LucideIcon::ChevronsRight,
            self::LessThan => LucideIcon::ChevronLeft,
            self::LessThanOrEqual => LucideIcon::ChevronsLeft,
            self::Equals => LucideIcon::Equal,
            self::NotEquals => LucideIcon::EqualNot,
        };
    }
}
