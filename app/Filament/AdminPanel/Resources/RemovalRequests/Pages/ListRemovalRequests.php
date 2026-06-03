<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests\Pages;

use App\Filament\AdminPanel\Resources\RemovalRequests\RemovalRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListRemovalRequests extends ListRecords
{
    protected static string $resource = RemovalRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
