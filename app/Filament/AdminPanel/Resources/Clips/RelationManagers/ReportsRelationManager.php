<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\RelationManagers;

use App\Filament\AdminPanel\Resources\Reports\ReportResource;
use Filament\Resources\RelationManagers\RelationManager;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    protected static ?string $relatedResource = ReportResource::class;

    public function isReadOnly(): bool
    {
        return true;
    }
}
