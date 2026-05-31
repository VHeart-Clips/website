<?php

declare(strict_types=1);

namespace App\Enums\Broadcaster;

use App\Enums\Filament\LucideIcon;
use App\Enums\Traits\HasTranslatedLabel;
use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RemovalRequestStatus: int implements HasColor, HasIcon, HasLabel
{
    use HasTranslatedLabel;

    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function isResolved(): bool
    {
        return $this !== self::Pending;
    }

    /**
     * @return RemovalRequestStatus[]
     */
    public function getUnresolvedCases(): array
    {
        return [
            self::Pending,
        ];
    }

    /**
     * @return RemovalRequestStatus[]
     */
    public function getResolvedCases(): array
    {
        return [
            self::Approved,
            self::Rejected,
        ];
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Pending => LucideIcon::Hourglass,
            self::Approved => LucideIcon::Check,
            self::Rejected => LucideIcon::X,
        };
    }

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'broadcaster.enums';
    }
}
