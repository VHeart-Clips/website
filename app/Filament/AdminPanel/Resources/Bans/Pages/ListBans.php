<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans\Pages;

use App\Filament\AdminPanel\Resources\Bans\BanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBans extends ListRecords
{
    protected static string $resource = BanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['admin_id'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
