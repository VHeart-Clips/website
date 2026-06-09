<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans\Pages;

use App\Filament\AdminPanel\Resources\Bans\Actions\UnbanAction;
use App\Filament\AdminPanel\Resources\Bans\BanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBan extends ViewRecord
{
    protected static string $resource = BanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            UnbanAction::make(),
        ];
    }
}
