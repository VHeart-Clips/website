<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Roles\Schemas;

use App\Enums\Permission;
use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...self::getBasicFields(),
                ...self::getPermissionSections(),
            ])
            ->columns(2);
    }

    private static function getBasicFields(): array
    {
        return [
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
        ];
    }

    private static function getPermissionSections(): array
    {
        return collect(Permission::cases())
            ->groupBy(fn (Permission $p): string => $p->getPermissionGroup())
            ->map(fn (Collection $permissions, string $group): Section => Section::make($group)
                ->compact()
                ->schema([
                    CheckboxList::make('permissions_'.Str::slug($group))
                        ->hiddenLabel()
                        ->bulkToggleable()
                        ->dehydrated(false)
                        ->options($permissions->mapWithKeys(fn (Permission $p): array => [$p->value => $p->getLabel()]))
                        ->formatStateUsing(fn (?Role $record): array => self::loadGroupPermissions($record, $permissions))
                        ->saveRelationshipsUsing(fn (?Role $record, ?array $state) => self::saveGroupPermissions($record, $state, $permissions))
                        ->columns()
                        ->gridDirection('row'),
                ])
            )
            ->values()
            ->toArray();
    }

    private static function loadGroupPermissions(?Role $record, Collection $permissions): array
    {
        $groupPermissions = $permissions->map(fn (Permission $p) => $p->value)->toArray();

        return DB::table('role_permissions')
            ->where('role_id', $record?->id)
            ->whereIn('permission', $groupPermissions)
            ->pluck('permission')
            ->toArray();
    }

    private static function saveGroupPermissions(Role $record, ?array $state, Collection $permissions): void
    {
        $currentUser = auth()->user();
        $submittedPermissions = collect($state ?? [])
            ->map(fn (Permission|string $p) => $p instanceof Permission ? $p->value : $p);

        $groupPermissions = $permissions->map(fn (Permission $p) => $p->value);

        $immutablePermissions = $permissions
            ->filter(fn (Permission $p): bool => ! $currentUser->can($p->value))
            ->map(fn (Permission $p) => $p->value);

        $existingRolePermissions = DB::table('role_permissions')
            ->where('role_id', $record->id)
            ->whereIn('permission', $groupPermissions)
            ->pluck('permission');

        $keepRestricted = $existingRolePermissions->intersect($immutablePermissions);
        $mutablePermissions = $submittedPermissions->diff($immutablePermissions);
        $finalPermissions = $keepRestricted->merge($mutablePermissions)->unique();

        $rows = $finalPermissions->map(fn (string $permission): array => [
            'role_id' => $record->id,
            'permission' => $permission,
        ])->toArray();

        $record->permissions()->whereIn('permission', $groupPermissions)->delete();

        if (! empty($rows)) {
            $record->permissions()->createMany($rows);
        }
    }
}
