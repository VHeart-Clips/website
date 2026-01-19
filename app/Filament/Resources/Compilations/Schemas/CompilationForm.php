<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compilations\Schemas;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip\Compilation;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('admin/resources/compilations.form.slug')
                            ->translateLabel()
                            ->required()
                            ->alphaDash()
                            ->unique(Compilation::class, 'slug')
                            ->maxLength(255)
                            ->afterContent(
                                Action::make('generateSlug')
                                    ->tooltip(__('admin/resources/compilations.action.generate-slug'))
                                    ->icon(Heroicon::Link)
                                    ->iconButton()
                                    ->action(function (Get $schemaGet, Set $schemaSet) {
                                        $schemaSet('slug', str($schemaGet('title'))->trim()->slug()->toString());
                                    })
                                // TODO: we should have the correct filament version but jsAction does not exist yet lol
                                // Action::make('generateSlug')->jsAction(<<<'JS'
                                //    $set('slug', $get('title').toLowerCase().replaceAll(' ', '-'))
                                // JS)
                            ),
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
                        Select::make('type')
                            ->label('admin/resources/compilations.form.type')
                            ->translateLabel()
                            ->disabledOn('edit')
                            ->required()
                            ->options(CompilationType::class)
                            ->default(CompilationType::LongVideo),
                    ]),
            ])->columns(3);
    }
}
