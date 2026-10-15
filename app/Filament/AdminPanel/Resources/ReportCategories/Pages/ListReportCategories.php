<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\Pages;

use App\Filament\AdminPanel\Resources\ReportCategories\ReportCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListReportCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = ReportCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
