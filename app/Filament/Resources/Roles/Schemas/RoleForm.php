<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
                Section::make()
                    ->compact()
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Assigned Permissions')
                            ->dehydrated(false)
                            ->options(Permission::class)
                            ->formatStateUsing(function ($record) {
                                return $record?->permissions->pluck('permission')->toArray() ?? [];
                            })
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $rows = collect($state)->map(fn ($permission) => [
                                    'role_id' => $record->id,
                                    'permission' => $permission,
                                ])->toArray();

                                $record->permissions()->delete();
                                $record->permissions()->createMany($rows);
                            })
                            ->columns()
                            ->gridDirection('row'),
                    ])->columnSpanFull(),
            ]);
    }
}
