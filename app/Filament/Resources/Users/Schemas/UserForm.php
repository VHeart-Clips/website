<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                            ->prefixIcon(Heroicon::User)
                            ->disabled(),
                        Select::make('roles')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->orderByDesc('weight')
                            )
                            // Even though they are visible and selectable, make sure only roles below the user weight are mutable
                            ->saveRelationshipsUsing(function (User $record, $state) use ($canIgnoreWeight, $userWeight, $roleWeights): void {
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
                            ->prefixIcon(Heroicon::ShieldCheck)
                            ->preload()
                            ->searchable(),
                    ])
                    ->columnSpanFull(),

                Section::make('Overview')
                    ->icon('heroicon-o-shield-check')
                    ->visibleOn('view')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Checkbox::make('clip_permission')
                                    ->label('Clip Permission')
                                    ->helperText('Clips wont be accepted without Permission for this User')
                                    ->disabled(),

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

                        CheckboxList::make('rules')
                            ->label('Clip Submission Rules')
                            ->disabled()
                            ->options([
                                'userAllowList' => 'User Whitelist',
                                'userAllowMods' => 'Mod Only',
                                'userAllowVips' => 'VIP Only',
                            ])
                            ->descriptions([
                                'userAllowList' => 'Only users in the Whitelist can submit Clips',
                                'userAllowMods' => 'Only Moderators of this User can submit Clips',
                                'userAllowVips' => 'Only VIPs of this User can submit Clips',
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
