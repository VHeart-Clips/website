<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Tables;

use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\ClipVoteType;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
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
            ])->withCount([
                'votes as votes_jury' => function (Builder $query): void {
                    $query->where('type', ClipVoteType::Jury)->whereVoted(true);
                },
                'votes as votes_public' => function (Builder $query): void {
                    $query->where('type', ClipVoteType::Public)->whereVoted(true);
                },
            ]))
            ->columns([
                Split::make([
                    Stack::make([
                        ImageColumn::make('thumbnail_url')
                            ->label('admin/resources/clips.table.columns.thumbnail')
                            ->translateLabel()
                            ->imageHeight(100)
                            ->alignCenter()
                            ->extraImgAttributes([
                                'class' => 'object-cover rounded aspect-video',
                                'loading' => 'lazy',
                            ]),
                    ])->grow(false),

                    Stack::make([
                        TextColumn::make('title')
                            ->label('admin/resources/clips.table.columns.title')
                            ->translateLabel()
                            ->weight('bold')
                            ->searchable()
                            ->wrap(),

                        Split::make([
                            TextColumn::make('duration')
                                ->label(__('admin/resources/clips.table.columns.duration'))
                                ->tooltip(__('admin/resources/clips.table.columns.duration'))
                                ->icon(Heroicon::Clock)
                                ->size(TextSize::Medium)
                                ->sortable()
                                ->formatStateUsing(fn (int $state): string => gmdate('i:s', $state))
                                ->fontFamily(FontFamily::Mono)
                                ->badge()
                                ->color('gray'),

                            TextColumn::make('votes_jury')
                                ->tooltip(__('admin/resources/clips.table.columns.votes_jury'))
                                ->label(__('admin/resources/clips.table.columns.votes_jury'))
                                ->icon(Heroicon::Star)
                                ->size(TextSize::Medium)
                                ->sortable()
                                ->badge()
                                ->color('warning'),
                            TextColumn::make('votes_public')
                                ->label(__('admin/resources/clips.table.columns.votes_public'))
                                ->tooltip(__('admin/resources/clips.table.columns.votes_public'))
                                ->size(TextSize::Medium)
                                ->icon(Heroicon::UserGroup)
                                ->sortable()
                                ->badge()
                                ->color('success'),
                            TextColumn::make('status')
                                ->label('admin/resources/clips.table.columns.status')
                                ->tooltip(__('admin/resources/clips.table.columns.status'))
                                ->size(TextSize::Medium)
                                ->icon(Heroicon::Clipboard)
                                ->badge()
                                ->translateLabel(),
                        ])->grow(false),
                    ])->space(),

                    Stack::make([
                        TextColumn::make('broadcaster.name')
                            ->tooltip(__('admin/resources/clips.table.columns.broadcaster'))
                            ->icon(Heroicon::VideoCamera)
                            ->color('gray'),

                        TextColumn::make('creator.name')
                            ->tooltip(__('admin/resources/clips.table.columns.creator'))
                            ->icon(Heroicon::Scissors)
                            ->color('gray'),

                        TextColumn::make('submitter.name')
                            ->tooltip(__('admin/resources/clips.table.columns.submitter'))
                            ->icon(Heroicon::User)
                            ->color('gray'),
                    ])
                        ->space(1),

                    Stack::make([
                        TextColumn::make('date')
                            ->label(__('admin/resources/clips.table.columns.created_at'))
                            ->tooltip(__('admin/resources/clips.table.columns.created_at'))
                            ->icon(Heroicon::Calendar)
                            ->dateTime()
                            ->sortable()
                            ->color('gray'),
                        TextColumn::make('created_at')
                            ->label(__('admin/resources/clips.table.columns.submitted_at'))
                            ->tooltip(__('admin/resources/clips.table.columns.submitted_at'))
                            ->icon(Heroicon::Calendar)
                            ->dateTime()
                            ->sortable()
                            ->color('gray'),

                        Split::make([
                            ImageColumn::make('category.box_art')
                                ->imageHeight(40)
                                ->alignCenter()
                                ->getStateUsing(fn (Clip $record) => $record->category?->getBoxArt())
                                ->extraImgAttributes([
                                    'class' => 'object-cover rounded-md aspect-[3/4]',
                                ])
                                ->grow(false),
                            TextColumn::make('category.title')
                                ->label('admin/resources/clips.table.columns.category')
                                ->translateLabel()
                                ->weight('medium')
                                ->wrap()
                                ->color('gray')
                                ->searchable(),
                        ])
                            ->grow(false),
                    ])
                        ->space(1),
                ])->from('lg'),
            ])
            ->filters([
                SelectFilter::make('broadcaster')
                    ->relationship('broadcaster', 'name', fn (Builder $query): Builder => $query->whereHas('broadcastedClips'))
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
            ])
            ->defaultSort('votes_public', 'desc')
            ->recordActions([
                ActionGroup::make([
                    Action::make('attach_to_compilation')
                        ->label('admin/resources/clips.actions.attach_to_compilation.label')
                        ->translateLabel()
                        ->icon(Heroicon::Link)
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
                                        ->options(CompilationClipStatus::class)
                                        ->default(CompilationClipStatus::Pending)
                                        ->required(),
                                ])->columns(1),
                        ])
                        ->action(function (Clip $record, array $data): void {
                            $record->compilations()->attach($data['compilation_id'], [
                                'status' => $data['status'] ?? CompilationClipStatus::Pending,
                                'claimed_by' => $data['claim'] ? auth()->id() : null,
                            ]);
                        })
                        ->successNotificationTitle(__('admin/resources/clips.notifications.actions.attached_to_compilation')),
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ]);
    }
}
