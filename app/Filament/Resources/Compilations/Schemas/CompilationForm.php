<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compilations\Schemas;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip\Compilation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompilationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                $newSlug = Str::slug($old ?? '');
                                $currentSlug = $get('slug') ?? '';

                                if (! empty($currentSlug) && $currentSlug !== $newSlug) {
                                    return;
                                }

                                $set('slug', $newSlug);
                            })
                            ->live(onBlur: true)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(Compilation::class, 'slug')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        TextInput::make('youtube_url')
                            ->url()
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(2)->columnSpan(2),
                Section::make()
                    ->schema([
                        Select::make('user_id')
                            ->label('Created by')
                            ->default(auth()->id())
                            ->relationship('user', 'name')
                            ->searchable(),
                        Select::make('status')
                            ->required()
                            ->options(CompilationStatus::class),
                        TextInput::make('auto_fill_seconds')
                            ->disabled() // currently does nothing, low priority for now
                            ->label('Auto Fill Seconds')
                            ->integer()
                            ->belowLabel('Automatically fill the Compilation with Clips to reach the set Minimum amount of Seconds, does nothing if left empty.'),
                        Select::make('type')
                            ->hiddenOn('edit')
                            ->required()
                            ->options(CompilationType::class)
                            ->default(CompilationType::Manual),
                    ]),
            ])->columns(3);
    }
}
