<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\Pages;

use App\Filament\AdminPanel\Resources\ReportCategories\ReportCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateReportCategory extends CreateRecord
{
    use Translatable;

    protected static string $resource = ReportCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
