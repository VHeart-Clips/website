<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqEntries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FaqEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->reactive()
                    ->minLength(10)
                    ->maxLength(255)
                    ->label('admin/resources/faq-entry.form.question.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/faq-entry.form.question.placeholder')),

                DateTimePicker::make('published_at')
                    ->seconds(false)
                    ->label('admin/resources/faq-entry.form.published_at.label')
                    ->translateLabel(),

                MarkdownEditor::make('body')
                    ->label('admin/resources/faq-entry.form.body.label')
                    ->translateLabel()
                    ->hint(__('admin/resources/faq-entry.form.body.hint'))
                    ->required(fn (Get $get): bool => ! empty($get('title')))
                    ->minLength(10)
                    ->maxLength(4000)
                    ->label('admin/resources/faq-entry.form.body.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/faq-entry.form.body.placeholder'))
                    ->columnSpanFull(),
            ]);
    }
}
