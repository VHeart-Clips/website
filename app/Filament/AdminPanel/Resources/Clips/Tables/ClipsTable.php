<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Tables;

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Clips\ClipStatus;
use App\Enums\Clips\CompilationStatus;
use App\Enums\Filament\LucideIcon;
use App\Filament\Actions\Tables\UpdateClipStatusAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Management\AttachToCompilationAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Management\ClipFeedbackAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\FlagClipAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\UnflagClipAction;
use App\Filament\AdminPanel\Resources\Clips\Pages\ListClips;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Resources\Clips\ClipActions;
use App\Filament\Resources\Clips\Tables\ClipColumns;
use App\Models\Clip\Compilation;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
                            ClipColumns::score(),
                            ClipColumns::status(),
                        ])->grow(false),

                        ClipColumns::tags(),
                    ])->space(),

                    Stack::make([
                        ClipColumns::juryVotes(),
                        ClipColumns::publicVotes(),
                        ClipColumns::impressions(),
                    ])
                        ->grow(false)
                        ->space(),

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
                self::makeCompilationUniqueBroadcasterFilter(),

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

                SelectFilter::make('has_consent')
                    ->options(BroadcasterConsent::class)
                    ->multiple()
                    ->searchable()
                    ->label('admin/resources/clips.filters.has_consent.label')
                    ->translateLabel()
                    ->query(function (Builder $query, array $data): Builder {
                        $values = collect($data['values'] ?? [])
                            ->map(fn (string $value) => BroadcasterConsent::tryFrom((int) $value))
                            ->filter();

                        if ($values->isEmpty()) {
                            return $query;
                        }

                        return $query->whereBroadcasterGavePermission($values->all());
                    }),

                TernaryFilter::make('has_consent_simple')
                    ->label('admin/resources/clips.filters.has_consent_simple.label')
                    ->translateLabel()
                    ->nullable()
                    ->placeholder(__('admin/resources/clips.filters.has_consent_simple.options.default'))
                    ->trueLabel(__('admin/resources/clips.filters.has_consent_simple.options.true'))
                    ->falseLabel(__('admin/resources/clips.filters.has_consent_simple.options.false'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereBroadcasterDeniedPermission(),
                        false: fn (Builder $query): Builder => $query,
                        blank: fn (Builder $query) => $query->whereBroadcasterGavePermission(),
                    ),

                TrashedFilter::make(),

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
                ClipActions::reportableActionGroup(),
                ActionGroup::make([
                    UpdateClipStatusAction::make()->moderation(),
                    ClipFeedbackAction::make(),
                    FlagClipAction::make(),
                    UnflagClipAction::make(),
                    AttachToCompilationAction::make(),
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ]);
    }

    /**
     * Allows the user to hide clips by broadcasters that are already within the selected Compilation
     */
    private static function makeCompilationUniqueBroadcasterFilter(): Filter
    {
        return Filter::make('unique_broadcaster_compilation')
            ->schema([
                Select::make('compilation_id')
                    ->label('Unique by Compilation')
                    ->hintIcon(LucideIcon::CircleQuestionMark, 'Hides any broadcaster who is already within the selected Compilation')
                    ->options(fn () => Compilation::query()
                        ->orderByDesc('created_at')
                        ->pluck('title', 'id'))
                    ->optionsLimit(5)
                    ->searchable()
                    ->preload(),
            ])
            ->query(function (Builder $query, array $data, ListClips $livewire): Builder {
                /** @var ?string $compilationId */
                $compilationId = $data['compilation_id'] ?? null;

                if (blank($compilationId)) {
                    return $query;
                }

                if (! is_numeric($compilationId) || Compilation::whereKey($compilationId)->doesntExist()) {
                    // removeTableFilter() doesnt update the url or filter input so we get a weird state
                    // setting the filter manually to null on the array itself will properly update though
                    $livewire->tableFilters['unique_broadcaster_compilation'] = null;

                    return $query;
                }

                return $query->whereNotIn('broadcaster_id', function (QueryBuilder $sub) use ($compilationId): void {
                    $sub->select('clips.broadcaster_id')
                        ->from('clips')
                        ->join('compilation_clip', 'clips.id', '=', 'compilation_clip.clip_id')
                        ->where('compilation_clip.compilation_id', $compilationId);
                });
            })
            ->indicateUsing(function (array $data): array {
                $compilationId = $data['compilation_id'] ?? null;

                if (! is_numeric($compilationId) || blank($compilationId)) {
                    return [];
                }

                $title = Compilation::whereKey($compilationId)->value('title');

                return $title ? [
                    'Not In Compilation: '.$title,
                ] : [];
            });
    }
}
