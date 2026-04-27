<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\RelationManagers;

class BroadcastedClipsRelationManager extends BaseUserClipsRelationManager
{
    protected static string $relationship = 'broadcastedClips';

    protected static ?string $title = 'Broadcasted Clips';
}
