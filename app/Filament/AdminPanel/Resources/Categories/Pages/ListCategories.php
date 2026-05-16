<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Categories\Pages;

use App\Filament\AdminPanel\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\CategorySelect;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema([
                    CategorySelect::make('id')
                        ->label('Category')
                        ->columnSpanFull()
                        ->required(),
                ])
                ->using(function (array $data, string $model): Category {
                    $existing = $model::where('id', $data['id'])
                        ->first();
                    if ($existing) {

                        return $existing;
                    }
                    Notification::make()
                        ->title('Error Importing Category')
                        ->danger()
                        ->send();

                    $this->halt();

                    return $model::create($data);
                }),
        ];
    }
}
