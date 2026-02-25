<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Livewire\Component;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(20)
                    ->minLength(2)
                    ->unique(
                        table: 'tags',
                        column: fn (Component $livewire): string => 'name->'.$livewire->activeLocale,
                        ignoreRecord: true
                    ),
            ])->columns(1);
    }
}
