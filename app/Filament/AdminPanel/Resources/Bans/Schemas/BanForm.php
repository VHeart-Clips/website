<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans\Schemas;

use App\Filament\Resources\Users\UserSelect;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class BanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FusedGroup::make([
                    Select::make('bannable_type')
                        ->required()
                        ->options([
                            'user' => 'User',
                            'broadcaster' => 'Broadcaster',
                        ]),
                    UserSelect::make('bannable_id')
                        ->columnSpan(3)
                        ->required(),
                ])
                    ->hiddenOn(Operation::Edit)
                    ->label('User / Broadcaster')
                    ->columnSpanFull()
                    ->columns(4),
                DateTimePicker::make('banned_until')
                    ->columnSpanFull(),
                MarkdownEditor::make('reason')
                    ->required()
                    ->minLength(10)
                    ->maxLength(1000 * 50)
                    ->columnSpanFull(),
            ]);
    }
}
