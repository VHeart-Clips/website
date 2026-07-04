<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\SharedRelationManagers;

use App\Filament\AdminPanel\Resources\Audits\AuditResource;
use Filament\Resources\RelationManagers\RelationManager;

class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $relatedResource = AuditResource::class;

    public function isReadOnly(): bool
    {
        return true;
    }
}
