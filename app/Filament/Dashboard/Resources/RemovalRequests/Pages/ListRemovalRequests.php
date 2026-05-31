<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\RemovalRequests\Pages;

use App\Filament\Dashboard\Resources\RemovalRequests\RemovalRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRemovalRequests extends ListRecords
{
    protected static string $resource = RemovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
