<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\SharedRelationManagers;

use App\Filament\AdminPanel\Resources\Bans\BanResource;
use Filament\Resources\RelationManagers\RelationManager;

class BansRelationManager extends RelationManager
{
    protected static string $relationship = 'bans';

    protected static ?string $relatedResource = BanResource::class;

    public function isReadOnly(): bool
    {
        return true;
    }
}
