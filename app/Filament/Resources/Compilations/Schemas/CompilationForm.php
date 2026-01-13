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
                            ->label('admin/resources/compilations.form.title')
                            ->translateLabel()
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
                            ->label('admin/resources/compilations.form.slug')
                            ->translateLabel()
                            ->required()
                            ->unique(Compilation::class, 'slug')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('admin/resources/compilations.form.description')
                            ->translateLabel()
                            ->columnSpanFull(),
                        TextInput::make('youtube_url')
                            ->label('admin/resources/compilations.form.youtube_url')
                            ->translateLabel()
                            ->url()
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(2)->columnSpan(2),
                Section::make()
                    ->schema([
                        Select::make('user_id')
                            ->label('admin/resources/compilations.form.created_by')
                            ->translateLabel()
                            ->default(auth()->id())
                            ->relationship('user', 'name')
                            ->searchable(),
                        Select::make('status')
                            ->label('admin/resources/compilations.form.status')
                            ->translateLabel()
                            ->required()
                            ->options(CompilationStatus::class),
                        TextInput::make('auto_fill_seconds')
                            ->disabled() // currently does nothing, low priority for now
                            ->label('admin/resources/compilations.form.auto_fill_seconds')
                            ->translateLabel()
                            ->integer()
                            ->belowLabel(__('admin/resources/compilations.form.auto_fill_seconds_helper')),
                        Select::make('type')
                            ->label('admin/resources/compilations.form.type')
                            ->translateLabel()
                            ->hiddenOn('edit')
                            ->required()
                            ->options(CompilationType::class)
                            ->default(CompilationType::Manual),
                    ]),
            ])->columns(3);
    }
}
