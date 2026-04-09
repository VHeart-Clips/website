<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Tables;

use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Clips\Actions\Management\ClipFeedbackAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\FlagClipAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\UnflagClipAction;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Resources\Clips\Tables\ClipColumns;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'category',
                'broadcaster',
                'creator',
                'submitter',
            ]))
            ->columns([
                Split::make([
                    Stack::make([
                        ClipColumns::thumbnail(),
                    ])->grow(false),

                    Stack::make([
                        ClipColumns::title(),

                        Split::make([
                            ClipColumns::duration(),
                            ClipColumns::voteStatistics(),
                            ClipColumns::status(),
                        ])->grow(false),

                        ClipColumns::tags(),
                    ])->space(),

                    Stack::make([
                        ClipColumns::broadcasterName(),
                        ClipColumns::creatorName(),
                        ClipColumns::submitterName(),
                    ])
                        ->space(1),

                    Stack::make([
                        ClipColumns::createdAt(),
                        ClipColumns::submittedAt(),
                        ClipColumns::category(),
                    ])
                        ->space(1),
                ])->from('lg'),
            ])
            ->filters([
                SelectFilter::make('broadcaster')
                    ->relationship('owner', 'name', fn (Builder $query): Builder => $query->whereHas('broadcastedClips'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/clips.filters.broadcaster')
                    ->translateLabel(),
                SelectFilter::make('creator')
                    ->relationship('creator', 'name', fn (Builder $query): Builder => $query->whereHas('createdClips'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/clips.filters.creator')
                    ->translateLabel(),
                SelectFilter::make('submitter')
                    ->relationship('submitter', 'name', fn (Builder $query): Builder => $query->whereHas('submittedClips'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/clips.filters.submitter')
                    ->translateLabel(),
                SelectFilter::make('category')
                    ->relationship('category', 'title')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/clips.filters.category')
                    ->translateLabel(),

                SelectFilter::make('status')
                    ->options(ClipStatus::class)
                    ->searchable()
                    ->label('admin/resources/clips.filters.status')
                    ->translateLabel(),

                TernaryFilter::make('status_visibility')
                    ->label('admin/resources/clips.filters.status_visibility.label')
                    ->translateLabel()
                    ->placeholder(__('admin/resources/clips.filters.status_visibility.placeholder'))
                    ->trueLabel(__('admin/resources/clips.filters.status_visibility.true'))
                    ->falseLabel(__('admin/resources/clips.filters.status_visibility.false'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('status', [ClipStatus::Blocked, ClipStatus::NeedApproval]),
                        false: fn (Builder $query): Builder => $query,
                        blank: fn (Builder $query) => $query->whereNotIn('status', [ClipStatus::Blocked, ClipStatus::NeedApproval]),
                    ),

                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/clips.filters.tags')
                    ->translateLabel(),

                // TODO: use enum method for compilation status when merged later
                TernaryFilter::make('in_compilation')
                    ->label('admin/resources/clips.filters.in_compilation.label')
                    ->translateLabel()
                    ->nullable()
                    ->placeholder(__('admin/resources/clips.filters.in_compilation.only_without_compilation'))
                    ->trueLabel(__('admin/resources/clips.filters.in_compilation.only_with_compilation'))
                    ->falseLabel(__('admin/resources/clips.filters.in_compilation.with_compilation'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('compilations', fn (Builder $query): Builder => $query->whereIn('compilations.status', array_merge([
                            CompilationStatus::Planned,
                        ], CompilationStatus::getPublicCases()))),
                        false: fn (Builder $query): Builder => $query,
                        blank: fn (Builder $query) => $query->whereDoesntHave('compilations', fn (Builder $query): Builder => $query->whereIn('compilations.status', array_merge([
                            CompilationStatus::Planned,
                        ], CompilationStatus::getPublicCases()))),
                    ),

                DateRangeFilter::make('date')
                    ->withPresets()
                    ->indicatorLabel(__('admin/resources/clips.filters.created_range.indicator'))
                    ->label('admin/resources/clips.filters.created_range.label')
                    ->translateLabel(),

                DateRangeFilter::make('created_at')
                    ->withPresets()
                    ->indicatorLabel(__('admin/resources/clips.filters.submission_range.indicator2'))
                    ->label('admin/resources/clips.filters.submission_range.label')
                    ->translateLabel(),
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(4)
            ->defaultSort('score', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ClipFeedbackAction::make(),
                    FlagClipAction::make(),
                    UnflagClipAction::make(),
                    Action::make('attach_to_compilation')
                        ->label('admin/resources/clips.actions.attach_to_compilation.label')
                        ->translateLabel()
                        ->icon(LucideIcon::Link)
                        ->schema([
                            Select::make('compilation_id')
                                ->label('Compilation')
                                ->searchable()
                                ->options(fn (Clip $record) => Clip\Compilation::query()
                                    ->whereNotIn('id', $record->compilations()->pluck('compilations.id'))
                                    ->pluck('title', 'id'))
                                ->preload()
                                ->required(),
                            Fieldset::make()
                                ->schema([
                                    Toggle::make('claim')
                                        ->reactive()
                                        ->label('admin/resources/clips.actions.attach_to_compilation.claim')
                                        ->translateLabel(),
                                    Select::make('status')
                                        ->disabled(fn (Get $get): bool => $get('claim') !== true)
                                        ->label('admin/resources/clips.actions.attach_to_compilation.status')
                                        ->translateLabel()
                                        ->options(CompilationClipClaimStatus::class)
                                        ->default(CompilationClipClaimStatus::Pending)
                                        ->required(),
                                ])->columns(1),
                        ])
                        ->action(function (Clip $record, array $data): void {
                            $record->compilations()->attach($data['compilation_id'], [
                                'added_by' => auth()->id(),
                                'claim_status' => $data['status'] ?? CompilationClipClaimStatus::Pending,
                                'claimed_by' => $data['claim'] ? auth()->id() : null,
                                'claimed_at' => now(),
                            ]);
                        })
                        ->successNotificationTitle(__('admin/resources/clips.notifications.actions.attached_to_compilation')),
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ]);
    }
}
