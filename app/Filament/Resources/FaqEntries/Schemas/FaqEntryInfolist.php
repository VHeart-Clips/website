<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqEntries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FaqEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->reactive()
                    ->label('admin/resources/faq-entry.form.question.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/faq-entry.form.question.placeholder')),
                DateTimePicker::make('published_at')
                    ->label('admin/resources/faq-entry.form.published_at.label')
                    ->translateLabel(),

                MarkdownEditor::make('body')
                    ->label('admin/resources/faq-entry.form.body.label')
                    ->translateLabel()
                    ->label('admin/resources/faq-entry.form.body.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/faq-entry.form.body.placeholder'))
                    ->columnSpanFull(),
            ])->columns(1);
    }
}
