<?php

declare(strict_types=1);

namespace App\Enums\Eloquent;

use App\Enums\Filament\LucideIcon;
use App\Enums\Traits\HasHeadlineLabel;
use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SetOperator implements HasIcon, HasLabel
{
    use HasHeadlineLabel;

    case All;
    case Any;
    case AnyMissing;
    case None;
    case Exact;

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Any => LucideIcon::Filter,
            self::AnyMissing => LucideIcon::FilterX,
            self::All => LucideIcon::ListChecks,
            self::None => LucideIcon::Ban,
            self::Exact => LucideIcon::Crosshair,
        };
    }
}
