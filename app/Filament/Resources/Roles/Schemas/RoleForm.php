<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('desc'),
                TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('public')
                    ->required(),
            ]);
    }
}
