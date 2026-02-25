<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqEntries\Pages;

use App\Filament\Resources\FaqEntries\FaqEntryResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateFaqEntry extends CreateRecord
{
    use Translatable;

    protected static string $resource = FaqEntryResource::class;

    public function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
