<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\RelationManagers;

use App\Filament\AdminPanel\Resources\ReportCategories\Pages\ViewReportCategory;
use App\Filament\AdminPanel\Resources\Reports\ReportResource;
use App\Models\ReportCategory;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    protected static ?string $relatedResource = ReportResource::class;

    /**
     * @param  ReportCategory  $ownerRecord
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->parent_id === null && $pageClass === ViewReportCategory::class;
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
