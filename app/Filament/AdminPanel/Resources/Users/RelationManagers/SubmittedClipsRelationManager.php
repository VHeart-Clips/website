<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\RelationManagers;

class SubmittedClipsRelationManager extends BaseUserClipsRelationManager
{
    protected static string $relationship = 'submittedClips';

    protected static ?string $title = 'Submitted Clips';
}
