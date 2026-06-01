<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\RemovalRequests\Pages;

use App\Filament\Dashboard\Resources\RemovalRequests\RemovalRequestResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListRemovalRequests extends ListRecords
{
    protected static string $resource = RemovalRequestResource::class;

    public function getTitle(): string|Htmlable
    {
        return Filament::getTenant()->name.' - '.parent::getTitle();
    }

    public function getHeading(): string|Htmlable|null
    {
        return parent::getTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
