<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ShortUrls\Pages;

use App\Filament\AdminPanel\Resources\ShortUrls\ShortUrlResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShortUrls extends ManageRecords
{
    protected static string $resource = ShortUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
