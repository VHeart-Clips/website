<?php

namespace App\Filament\Resources\Compilations\Pages;

use App\Filament\Resources\Compilations\CompilationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompilations extends ListRecords
{
    protected static string $resource = CompilationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
