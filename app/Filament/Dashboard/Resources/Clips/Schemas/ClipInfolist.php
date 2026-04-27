<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Clips\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Filament\Infolists\Components\TwitchEmbedEntry;
use App\Models\Category;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;

class ClipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->compact()
                    ->schema([
                        TwitchEmbedEntry::make('twitch_id')
                            ->hiddenLabel()
                            ->alignCenter(),
                    ])
                    ->columnSpan(['default' => 3, 'lg' => 2]),

                Section::make()
                    ->compact()
                    ->schema([
                        TextEntry::make('title')
                            ->hiddenLabel()
                            ->weight('bold')
                            ->size(TextSize::Large),
                        TextEntry::make('tags.name')
                            ->label('dashboard/resources/clips.form.tags')
                            ->translateLabel()
                            ->color('gray')
                            ->size(TextSize::Large)
                            ->badge(),

                        Grid::make(4)
                            ->schema([
                                ImageEntry::make('category.box_art')
                                    ->hiddenLabel()
                                    ->state(fn (?Category $category): ?string => ($category ?? new Category(Category::Defaults))->getBoxArt())
                                    ->extraImgAttributes([
                                        'class' => 'object-cover rounded aspect-[3/4]',
                                    ])
                                    ->columnSpan(1)
                                    ->grow(false),
                                TextEntry::make('title')
                                    ->label('dashboard/resources/clips.infolist.category')
                                    ->translateLabel()
                                    ->columnSpan(3)
                                    ->hiddenLabel()
                                    ->size(TextSize::Medium)
                                    ->weight('medium'),
                            ])
                            ->relationship('category'),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('duration')
                                    ->label(__('dashboard/resources/clips.table.columns.duration'))
                                    ->tooltip(__('dashboard/resources/clips.table.columns.duration'))
                                    ->icon(LucideIcon::Clock)
                                    ->formatStateUsing(fn (int $state): string => gmdate('i:s', $state))
                                    ->fontFamily(FontFamily::Mono)
                                    ->size(TextSize::Medium)
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('absolute_votes')
                                    ->label(__('dashboard/resources/clips.table.columns.votes'))
                                    ->icon(LucideIcon::Users)
                                    ->size(TextSize::Medium)
                                    ->default(0)
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('status')
                                    ->label('dashboard/resources/clips.table.columns.status')
                                    ->tooltip(__('dashboard/resources/clips.table.columns.status'))
                                    ->size(TextSize::Medium)
                                    ->icon(LucideIcon::Clipboard)
                                    ->badge()
                                    ->translateLabel(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('dashboard/resources/clips.infolist.creator')
                                    ->translateLabel()
                                    ->icon(LucideIcon::Scissors)
                                    ->color('gray'),
                                TextEntry::make('submitter.name')
                                    ->label('dashboard/resources/clips.infolist.submitted_by')
                                    ->translateLabel()
                                    ->icon(LucideIcon::User)
                                    ->color('gray'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('date')
                                    ->date()
                                    ->label('dashboard/resources/clips.infolist.created_at')
                                    ->translateLabel()
                                    ->icon(LucideIcon::Calendar)
                                    ->color('gray'),
                                TextEntry::make('created_at')
                                    ->date()
                                    ->label('dashboard/resources/clips.infolist.submitted_at')
                                    ->translateLabel()
                                    ->icon(LucideIcon::Calendar)
                                    ->color('gray'),
                            ]),
                    ])
                    ->columnSpan(['default' => 3, 'lg' => 1]),
            ])
            ->columns(3);
    }
}
