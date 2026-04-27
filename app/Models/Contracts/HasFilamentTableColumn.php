<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use App\Filament\Tables\MorphColumn;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Layout\Component;

/**
 * Provides a way for {@see MorphColumn} to render the model on a morphable relationship column.
 */
interface HasFilamentTableColumn
{
    public static function getFilamentTableColumn(string $name): Component|Column;
}
