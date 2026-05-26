<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Clips\Pages;

use App\Filament\Dashboard\Resources\Clips\ClipResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListClips extends ListRecords
{
    protected static string $resource = ClipResource::class;

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
        return [];
    }
}
