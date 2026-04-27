<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Clips\Tables;

use App\Enums\Clips\ClipStatus;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Resources\Clips\ClipActions;
use App\Filament\Resources\Clips\Tables\ClipColumns;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'category',
                'broadcaster',
                'creator',
                'submitter',
            ])->withAbsoluteVoteCount())
            ->columns([
                Split::make([
                    Stack::make([
                        ClipColumns::thumbnail()
                            ->label('dashboard/resources/clips.table.columns.thumbnail'),
                    ])->grow(false),

                    Stack::make([
                        ClipColumns::title()
                            ->label('dashboard/resources/clips.table.columns.title'),
                        Split::make([
                            ClipColumns::duration()
                                ->label(__('dashboard/resources/clips.table.columns.duration'))
                                ->tooltip(__('dashboard/resources/clips.table.columns.duration')),
                            ClipColumns::publicVotes('absolute_votes')
                                ->label(__('dashboard/resources/clips.table.columns.votes'))
                                ->tooltip(__('dashboard/resources/clips.table.columns.votes')),
                            ClipColumns::status()
                                ->label('dashboard/resources/clips.table.columns.status')
                                ->tooltip(__('dashboard/resources/clips.table.columns.status')),
                        ])->grow(false),

                        ClipColumns::tags(),
                    ])->space(),

                    Stack::make([
                        ClipColumns::creatorName()
                            ->tooltip(__('dashboard/resources/clips.table.columns.creator')),
                        ClipColumns::submitterName()
                            ->tooltip(__('dashboard/resources/clips.table.columns.submitter')),
                    ])
                        ->space(1),

                    Stack::make([
                        ClipColumns::createdAt()
                            ->label(__('dashboard/resources/clips.table.columns.created_at'))
                            ->tooltip(__('dashboard/resources/clips.table.columns.created_at')),
                        ClipColumns::submittedAt()
                            ->label(__('dashboard/resources/clips.table.columns.submitted_at'))
                            ->tooltip(__('dashboard/resources/clips.table.columns.submitted_at')),

                        Split::make([
                            ClipColumns::categoryImage(),
                            ClipColumns::categoryName()
                                ->label('dashboard/resources/clips.table.columns.category'),
                        ])
                            ->grow(false),
                    ])
                        ->space(1),
                ])->from('lg'),
            ])
            ->filters([
                SelectFilter::make('creator')
                    ->relationship('creator', 'name', fn (Builder $query): Builder => $query->whereHas('createdClips'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('dashboard/resources/clips.filters.creator')
                    ->translateLabel(),
                SelectFilter::make('submitter')
                    ->relationship('submitter', 'name', fn (Builder $query): Builder => $query->whereHas('submittedClips'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('dashboard/resources/clips.filters.submitter')
                    ->translateLabel(),
                SelectFilter::make('category')
                    ->relationship('category', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('dashboard/resources/clips.filters.category')
                    ->translateLabel(),

                SelectFilter::make('status')
                    ->options(ClipStatus::class)
                    ->multiple()
                    ->searchable()
                    ->label('dashboard/resources/clips.filters.status')
                    ->translateLabel(),

                TernaryFilter::make('status_visibility')
                    ->label('dashboard/resources/clips.filters.status_visibility.label')
                    ->translateLabel()
                    ->placeholder(__('dashboard/resources/clips.filters.status_visibility.placeholder'))
                    ->trueLabel(__('dashboard/resources/clips.filters.status_visibility.true'))
                    ->falseLabel(__('dashboard/resources/clips.filters.status_visibility.false'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('status', [ClipStatus::Blocked, ClipStatus::NeedApproval]),
                        false: fn (Builder $query) => $query->whereNotIn('status', [ClipStatus::Blocked, ClipStatus::NeedApproval]),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('dashboard/resources/clips.filters.tags')
                    ->translateLabel(),

                DateRangeFilter::make('date')
                    ->withPresets()
                    ->indicatorLabel(__('dashboard/resources/clips.filters.created_range.indicator'))
                    ->label('dashboard/resources/clips.filters.created_range.label')
                    ->translateLabel(),
                DateRangeFilter::make('created_at')
                    ->withPresets()
                    ->indicatorLabel(__('dashboard/resources/clips.filters.submission_range.indicator'))
                    ->label('dashboard/resources/clips.filters.submission_range.label')
                    ->translateLabel(),
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(3)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ClipActions::reportableActionGroup(),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ]);
    }
}
