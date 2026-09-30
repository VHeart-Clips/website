<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\Pages;

use App\Filament\AdminPanel\Resources\ReportCategories\ReportCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewReportCategory extends ViewRecord
{
    use Translatable;

    protected static string $resource = ReportCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make(),
        ];
    }
}
