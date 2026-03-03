<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Schemas;

use App\Enums\ClipVoteType;
use App\Filament\Infolists\Components\TwitchEmbedEntry;
use App\Filament\Resources\Users\UserResource;
use App\Models\Category;
use App\Models\Clip;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

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
                            ->label('admin/resources/clips.form.tags')
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
                                    ->label('admin/resources/clips.infolist.category')
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
                                    ->label(__('admin/resources/clips.table.columns.duration'))
                                    ->tooltip(__('admin/resources/clips.table.columns.duration'))
                                    ->icon(Heroicon::Clock)
                                    ->formatStateUsing(fn (int $state): string => gmdate('i:s', $state))
                                    ->fontFamily(FontFamily::Mono)
                                    ->size(TextSize::Medium)
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('votes_jury')
                                    ->label(__('admin/resources/clips.table.columns.votes_jury'))
                                    ->state(fn (Clip $record) => $record->votes()->where('type', ClipVoteType::Jury)->whereVoted(true)->count())
                                    ->icon(Heroicon::Star)
                                    ->size(TextSize::Medium)
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('votes_public')
                                    ->label(__('admin/resources/clips.table.columns.votes_public'))
                                    ->state(fn (Clip $record) => $record->votes()->where('type', ClipVoteType::Public)->whereVoted(true)->count())
                                    ->icon(Heroicon::UserGroup)
                                    ->size(TextSize::Medium)
                                    ->badge()
                                    ->color('success'),
                            ]),

                        Group::make([
                            TextEntry::make('broadcaster.name')
                                ->label('admin/resources/clips.infolist.broadcaster')
                                ->translateLabel()
                                ->icon(Heroicon::VideoCamera)
                                ->color('gray')
                                ->url(function (Clip $clip): ?string {
                                    if (! $clip->broadcaster?->exists) {
                                        return null;
                                    }

                                    return UserResource::getUrl('view', ['record' => $clip->broadcaster]);
                                })->openUrlInNewTab(),

                            TextEntry::make('creator.name')
                                ->label('admin/resources/clips.infolist.creator')
                                ->translateLabel()
                                ->icon(Heroicon::Scissors)
                                ->color('gray')
                                ->url(function (Clip $clip): ?string {
                                    if (! $clip->creator?->exists) {
                                        return null;
                                    }

                                    return UserResource::getUrl('view', ['record' => $clip->creator]);
                                })->openUrlInNewTab(),

                            TextEntry::make('submitter.name')
                                ->label('admin/resources/clips.infolist.submitted_by')
                                ->translateLabel()
                                ->icon(Heroicon::User)
                                ->color('gray')
                                ->url(function (Clip $clip): ?string {
                                    if (! $clip->submitter?->exists) {
                                        return null;
                                    }

                                    return UserResource::getUrl('view', ['record' => $clip->submitter]);
                                })->openUrlInNewTab(),
                        ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('date')
                                    ->date()
                                    ->label('admin/resources/clips.infolist.created_at')
                                    ->translateLabel()
                                    ->icon(Heroicon::Calendar)
                                    ->color('gray'),
                                TextEntry::make('created_at')
                                    ->date()
                                    ->label('admin/resources/clips.infolist.submitted_at')
                                    ->translateLabel()
                                    ->icon(Heroicon::Calendar)
                                    ->color('gray'),
                            ]),
                    ])
                    ->columnSpan(['default' => 3, 'lg' => 1]),
            ])
            ->columns(3);
    }
}
