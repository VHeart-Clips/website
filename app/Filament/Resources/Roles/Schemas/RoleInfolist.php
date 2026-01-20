<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('admin/resources/roles.infolist.name')
                    ->translateLabel(),
                TextEntry::make('desc')
                    ->label('admin/resources/roles.infolist.desc')
                    ->translateLabel()
                    ->placeholder('-'),
                TextEntry::make('weight')
                    ->label('admin/resources/roles.infolist.weight')
                    ->translateLabel()
                    ->numeric(),
                IconEntry::make('public')
                    ->label('admin/resources/roles.infolist.public')
                    ->translateLabel()
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('admin/resources/roles.infolist.created_at')
                    ->translateLabel()
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('admin/resources/roles.infolist.updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
