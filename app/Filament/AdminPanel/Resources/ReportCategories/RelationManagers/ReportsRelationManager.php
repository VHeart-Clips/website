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
        if ($pageClass !== ViewReportCategory::class) {
            return false;
        }

        if ($ownerRecord->parent_id !== null) {
            return true;
        }

        // unless the category has reports itself we just hide it for main categories
        // and yes filament caches this before anyone asks why i didnt use once()
        return $ownerRecord->reports()->exists();
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
