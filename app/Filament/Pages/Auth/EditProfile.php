<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->disabled() // disabled for now as we cant send emails anyway haha
                    ->belowLabel('currently disabled because of technical reasons')
                    ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
                    ->email()
                    ->nullable()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(debounce: 500),
            ]);
    }
}
