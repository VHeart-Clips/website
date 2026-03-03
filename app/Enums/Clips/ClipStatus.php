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

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unknown => 'gray',
            self::NeedApproval => 'warning',
            self::Approved => 'success',
            self::Blocked => 'danger',
        };
    }

    private function getTranslatableEnumLabelPrefix(): string
    {
        return 'clips.enums';
    }
}
