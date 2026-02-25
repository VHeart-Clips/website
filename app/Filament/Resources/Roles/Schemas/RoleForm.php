<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\Permission;
use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('admin/resources/roles.form.name')
                    ->unique(
                        table: 'roles',
                        column: fn (Component $livewire): string => 'name->'.$livewire->activeLocale,
                        ignoreRecord: true
                    )
                    ->translateLabel()
                    ->required(),
                TextInput::make('desc')
                    ->label('admin/resources/roles.form.desc')
                    ->translateLabel(),
                TextInput::make('weight')
                    ->label('admin/resources/roles.form.weight')
                    ->translateLabel()
                    ->maxValue(fn (): int|float => (auth()->user()->getRole()?->weight ?? 0) - 1)
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
                            ->formatStateUsing(fn (?Role $record) => DB::table('role_permissions')
                                ->where('role_id', $record?->id)
                                ->pluck('permission')
                                ->toArray())
                            ->saveRelationshipsUsing(function ($record, $state): void {
                                $currentUser = auth()->user();
                                $submittedPermissions = collect($state)
                                    ->map(fn ($p) => $p instanceof Permission ? $p->value : $p);
                                $immutablePermissions = collect(Permission::cases())
                                    ->filter(fn (Permission $p): bool => ! $currentUser->can($p->value))
                                    ->map(fn (Permission $p) => $p->value);

                                $existingRolePermissions = DB::table('role_permissions')
                                    ->where('role_id', $record->id)
                                    ->pluck('permission');

                                $keepRestricted = $existingRolePermissions->intersect($immutablePermissions);
                                $mutablePermissions = $submittedPermissions->diff($immutablePermissions);
                                $finalPermissions = $keepRestricted->merge($mutablePermissions)->unique();

                                $rows = $finalPermissions->map(fn ($permission): array => [
                                    'role_id' => $record->id,
                                    'permission' => $permission,
                                ])->toArray();

                                $record->permissions()->delete();

                                if (! empty($rows)) {
                                    $record->permissions()->createMany($rows);
                                }
                            })
                            ->columns()
                            ->gridDirection('row'),
                    ])->columnSpanFull(),
            ]);
    }
}
