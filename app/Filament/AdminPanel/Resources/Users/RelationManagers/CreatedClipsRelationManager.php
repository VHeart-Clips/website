<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\RelationManagers;

class CreatedClipsRelationManager extends BaseUserClipsRelationManager
{
    protected static string $relationship = 'createdClips';

    protected static ?string $title = 'Clipped Clips';
}
