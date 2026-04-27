<?php

declare(strict_types=1);

namespace App\Enums\Clips;

use App\Enums\Traits\HasTranslatedLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ClipStatus: int implements HasColor, HasLabel
{
    use HasTranslatedLabel;

    case Unknown = 0;
    case NeedApproval = 1;
    case Approved = 2;
    case Blocked = 3;

    /**
     * @return ClipStatus[]
     */
    public static function defaultableOptions(): array
    {
        return [
            self::Approved,
            self::NeedApproval,
        ];
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unknown => 'gray',
            self::NeedApproval => 'warning',
            self::Approved => 'success',
            self::Blocked => 'danger',
        };
    }

    public function isWithheld(): bool
    {
        return match ($this) {
            self::NeedApproval, self::Blocked => true,
            default => false,
        };
    }

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'clips.enums';
    }
}
