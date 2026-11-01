<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Schemas;

use App\Models\Clip\Compilation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompilationInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('title')
                            ->label('admin/resources/compilations.form.title')
                            ->translateLabel(),
                        TextEntry::make('slug')
                            ->label('admin/resources/compilations.form.slug')
                            ->translateLabel(),
                        TextEntry::make('description')
                            ->markdown()
                            ->label('admin/resources/compilations.form.description')
                            ->translateLabel()
                            ->columnSpanFull(),
                        TextEntry::make('youtube_url')
                            ->label('admin/resources/compilations.form.youtube_url')
                            ->translateLabel()
                            ->columnSpanFull(),
                    ])->columns(2)->columnSpan(2),
                Section::make()
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('admin/resources/compilations.form.created_by')
                            ->translateLabel(),
                        TextEntry::make('status')
                            ->label('admin/resources/compilations.form.status')
                            ->translateLabel(),
                        TextEntry::make('type')
                            ->label('admin/resources/compilations.form.type')
                            ->translateLabel(),
                        TextEntry::make('processing_status')
                            ->visible(fn (Compilation $compilation): bool => $compilation->processing_status?->isProcessing() ?? false),
                    ]),
            ])->columns(3);
    }
}
