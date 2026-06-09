<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\Pages;

use App\Filament\Actions\ResourceLinkAction;
use App\Filament\AdminPanel\Actions\Ban\BanAction;
use App\Filament\AdminPanel\Resources\Broadcasters\BroadcasterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBroadcaster extends ViewRecord
{
    protected static string $resource = BroadcasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ResourceLinkAction::make('userLink')
                ->relationship('user')
                ->label('User'),
            EditAction::make(),
            BanAction::make(),
        ];
    }
}
