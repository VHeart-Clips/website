<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Users\RelationManagers;

use App\Enums\Clips\ClipStatus;
use App\Filament\AdminPanel\Resources\Clips\ClipResource;
use App\Filament\Resources\Clips\Tables\ClipColumns;
use App\Models\Clip;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

/**
 * @method User getOwnerRecord
 */
abstract class BaseUserClipsRelationManager extends RelationManager
{
    protected static string $relationship = '';

    protected static ?string $title = '';

    protected static ?string $relatedResource = ClipResource::class;

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'category',
                'broadcaster',
                'creator',
                'submitter',
            ])
                ->withScore()
                ->withPublicVoteCount()
                ->withJuryVoteCount()
            )
            ->recordTitleAttribute('title')
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
                    ])->space(),

                    Stack::make([
                        ClipColumns::broadcasterName(),
                        ClipColumns::creatorName(),
                        ClipColumns::submitterName(),
                    ])
                        ->space(1),

                    Stack::make([
                        ClipColumns::createdAt('clips.date')
                            ->getStateUsing(fn (Clip $clip) => $clip->date),
                        ClipColumns::submittedAt('clips.created_at')
                            ->getStateUsing(fn (Clip $clip) => $clip->created_at),

                        ClipColumns::category(),
                    ])
                        ->space(1),
                ])->from('lg'),
            ])
            ->filters([
                SelectFilter::make('broadcaster')
                    ->relationship('owner', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->{$this::$relationship}()->pluck('broadcaster_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/compilations.relation_managers.clips.filters.broadcaster')
                    ->translateLabel(),
                SelectFilter::make('creator')
                    ->relationship('creator', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->{$this::$relationship}()->pluck('creator_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/compilations.relation_managers.clips.filters.creator')
                    ->translateLabel(),
                SelectFilter::make('submitter')
                    ->relationship('submitter', 'name', fn (Builder $query) => $query->whereIn('id',
                        $this->getOwnerRecord()->{$this::$relationship}()->pluck('submitter_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/compilations.relation_managers.clips.filters.submitter')
                    ->translateLabel(),
                SelectFilter::make('category')
                    ->relationship('category', 'title',
                        fn (Builder $query) => $query->whereIn('id', $this->getOwnerRecord()->{$this::$relationship}()->pluck('category_id')))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('admin/resources/compilations.relation_managers.clips.filters.category')
                    ->translateLabel(),

                SelectFilter::make('clips.status')
                    ->label('admin/resources/compilations.relation_managers.clips.filters.clip_status')
                    ->translateLabel()
                    ->multiple()
                    ->options(ClipStatus::class),

                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                CommentsAction::make()
                    ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                    ->authorize('comment')
                    ->perPage(4)
                    ->loadMoreIncrementsBy(8)
                    ->modalWidth(Width::SevenExtraLarge),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ])
            ->deferLoading()
            ->openRecordUrlInNewTab();
    }
}
