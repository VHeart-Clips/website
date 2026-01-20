<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('admin/resources/roles.form.name')
                    ->translateLabel()
                    ->required(),
                TextInput::make('desc')
                    ->label('admin/resources/roles.form.desc')
                    ->translateLabel(),
                TextInput::make('weight')
                    ->label('admin/resources/roles.form.weight')
                    ->translateLabel()
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('public')
                    ->label('admin/resources/roles.form.public')
                    ->translateLabel()
                    ->required(),
                Section::make()
                    ->compact()
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('admin/resources/roles.form.permissions')
                            ->translateLabel()
                            ->dehydrated(false)
                            ->options(Permission::class)
                            ->formatStateUsing(function ($record) {
                                $rawPermissions = DB::table('role_permissions')
                                    ->where('role_id', $record->id)
                                    ->pluck('permission');

                                return $rawPermissions
                                    ->map(fn ($perm) => Permission::tryFrom($perm))
                                    ->filter()
                                    ->values()
                                    ->toArray();
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
