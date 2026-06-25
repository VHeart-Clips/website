<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $userWeight = auth()->user()->getRole()?->weight ?? 0;
        $canIgnoreWeight = auth()->user()->getRole()?->id === 0;
        /** @var Collection<array<int, int>> $roleWeights */
        $roleWeights = Role::pluck('weight', 'id');

        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->prefixIcon(LucideIcon::User)
                            ->disabled(),
                        Select::make('roles')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->orderByDesc('weight')
                            )
                            // Even though they are visible and selectable, make sure only roles below the user weight are mutable
                            ->saveRelationshipsUsing(function (User $record, array $state) use ($canIgnoreWeight, $userWeight, $roleWeights): void {
                                $immutableRoles = $roleWeights
                                    ->filter(fn (int $weight): bool => ! $canIgnoreWeight && $weight >= $userWeight)
                                    ->keys();
                                $existingRoleIds = $record->roles()->pluck('id');
                                $rolesToKeep = $existingRoleIds->intersect($immutableRoles);
                                $mutableRoles = collect($state)->diff($immutableRoles);
                                $record->roles()->sync($rolesToKeep->merge($mutableRoles));
                            })
                            ->label('Roles')
                            ->multiple()
                            ->prefixIcon(LucideIcon::ShieldCheck)
                            ->preload()
                            ->searchable(),
                    ])
                    ->columnSpanFull(),

                Section::make('Overview')
                    ->icon(LucideIcon::ShieldCheck)
                    ->visibleOn(Operation::View)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Checkbox::make('email')
                                    ->label('Email Set')
                                    ->helperText('Indicates if the user set email address.')
                                    ->disabled(),

                                Checkbox::make('email_verified_at')
                                    ->label('Email Verified')
                                    ->helperText('Indicates if the user has confirmed their email address.')
                                    ->disabled(),

                                Checkbox::make('2fa_enabled')
                                    ->formatStateUsing(fn (User $user): bool => ! empty($user->app_authentication_secret))
                                    ->dehydrated(false)
                                    ->label('2FA Active')
                                    ->helperText('Indicates if the user has enabled two factor authentication.')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
