<?php

declare(strict_types=1);

namespace App\Enums\Clips;

use App\Enums\Traits\HasHeadlineLabel;
use Filament\Support\Contracts\HasLabel;

/**
 * Based on the Type we decide on how to handle the Collection in the backend and frontend
 */
enum CollectionType: string implements HasLabel
{
    use HasHeadlineLabel;

    case Manual = 'manual';
}
