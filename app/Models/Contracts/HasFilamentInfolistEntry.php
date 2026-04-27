<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Filament\Schemas\Components\Component;

interface HasFilamentInfolistEntry
{
    public static function getFilamentInfolistEntry(string $name): Component;
}
