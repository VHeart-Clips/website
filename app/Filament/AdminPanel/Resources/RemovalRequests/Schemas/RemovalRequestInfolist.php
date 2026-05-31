<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\RemovalRequests\Schemas;

use App\Filament\Actions\ResourceLinkAction;
use App\Filament\Infolists\Components\TwitchEmbedEntry;
use App\Models\Broadcaster\RemovalRequest;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

class RemovalRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        TwitchEmbedEntry::make('clip.twitch_id')
                            ->hiddenLabel(),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('clip.title')
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->hiddenLabel()
                                    ->wrap(),

                                TextEntry::make('status')
                                    ->label('admin/resources/removal-requests.infolist.status')
                                    ->translateLabel(),

                                Grid::make()
                                    ->schema([
                                        TextEntry::make('claimer.name')
                                            ->label('admin/resources/removal-requests.infolist.claimed_by.label')
                                            ->placeholder(__('admin/resources/removal-requests.infolist.claimed_by.placeholder'))
                                            ->hintAction(
                                                ResourceLinkAction::make('claimerLink')
                                                    ->openUrlInNewTab()
                                                    ->relationship('claimer')
                                            )
                                            ->translateLabel(),
                                        TextEntry::make('claimed_at')
                                            ->hint(fn (RemovalRequest $record) => $record->created_at->diffForHumans($record->claimed_at ?? now(), true))
                                            ->label('admin/resources/removal-requests.infolist.claimed_at')
                                            ->translateLabel()
                                            ->dateTime(),

                                        TextEntry::make('resolver.name')
                                            ->label('admin/resources/removal-requests.infolist.resolved_by.label')
                                            ->placeholder(__('admin/resources/removal-requests.infolist.resolved_by.placeholder'))
                                            ->hintAction(
                                                ResourceLinkAction::make('resolverLink')
                                                    ->openUrlInNewTab()
                                                    ->relationship('resolver')
                                            )
                                            ->translateLabel(),
                                        TextEntry::make('resolved_at')
                                            ->hint(fn (RemovalRequest $record) => $record->created_at->diffForHumans($record->resolved_at ?? now(), true))
                                            ->label('admin/resources/removal-requests.infolist.resolved_at')
                                            ->translateLabel()
                                            ->dateTime(),
                                    ]),

                                TextEntry::make('details')
                                    ->label('admin/resources/removal-requests.infolist.details.label')
                                    ->translateLabel()
                                    ->placeholder(__('admin/resources/removal-requests.infolist.details.placeholder')),
                            ]),
                    ])->columnSpanFull(),

                CommentsEntry::make('discussion')
                    ->hint(__('admin/resources/removal-requests.infolist.discussion.hint'))
                    ->columnSpanFull(),
            ]);
    }
}
