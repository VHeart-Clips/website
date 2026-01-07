<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->prefixIcon(Heroicon::User)
                            ->disabled(),
                        Select::make('roles')
                            ->relationship('roles', 'name')
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

                                Checkbox::make('two_factor_confirmed_at')
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
