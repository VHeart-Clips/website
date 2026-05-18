<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Tables;

use App\Enums\Filament\LucideIcon;
use App\Models\Clip;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\ViteException;
use Illuminate\Support\Facades\Vite;

class ClipColumns
{
    public static function thumbnail(): ImageColumn
    {
        try {
            $placeholder = Vite::asset('resources/images/webp/clips/no_thumbnail.webp');
        } catch (ViteException) {
            // Vite crashing everything is worse than a broken image
            $placeholder = '';
        }

        return ImageColumn::make('thumbnail_url')
            ->label('admin/resources/clips.table.columns.thumbnail')
            ->translateLabel()
            ->imageHeight(100)
            ->alignCenter()
            ->getStateUsing(fn (Clip $clip): ?string => $clip->proxiedContentUrl())
            ->defaultImageUrl($placeholder)
            ->extraImgAttributes([
                'class' => "object-cover rounded aspect-video text-transparent relative after:content-[''] after:absolute after:inset-0 after:bg-[image:var(--placeholder-url)] after:bg-cover after:bg-center",
                'style' => "--placeholder-url: url('{$placeholder}');",
                'loading' => 'lazy',
                'onerror' => "this.onerror=null;this.src='{$placeholder}';",
            ]);
    }

    public static function title(): TextColumn
    {
        return TextColumn::make('title')
            ->label('admin/resources/clips.table.columns.title')
            ->translateLabel()
            ->weight('bold')
            ->searchable()
            ->wrap();
    }

    public static function duration(): TextColumn
    {
        return TextColumn::make('duration')
            ->label(__('admin/resources/clips.table.columns.duration'))
            ->tooltip(__('admin/resources/clips.table.columns.duration'))
            ->icon(LucideIcon::Clock)
            ->size(TextSize::Medium)
            ->sortable()
            ->formatStateUsing(fn (int $state): string => round($state).'s')
            ->fontFamily(FontFamily::Mono)
            ->badge()
            ->color('gray');
    }

    public static function juryVotes(string $column = 'jury_votes'): TextColumn
    {
        return TextColumn::make($column)
            ->tooltip(__('admin/resources/clips.table.columns.jury_votes'))
            ->label(__('admin/resources/clips.table.columns.jury_votes'))
            ->icon(LucideIcon::Star)
            ->size(TextSize::Medium)
            ->sortable()
            ->badge()
            ->color('warning');
    }

    public static function publicVotes(string $column = 'public_votes'): TextColumn
    {
        return TextColumn::make($column)
            ->label(__('admin/resources/clips.table.columns.public_votes'))
            ->tooltip(__('admin/resources/clips.table.columns.public_votes'))
            ->size(TextSize::Medium)
            ->icon(LucideIcon::Users)
            ->sortable()
            ->badge()
            ->color('success');
    }

    public static function absoluteVotes(string $column = 'absolute_votes'): TextColumn
    {
        return TextColumn::make($column)
            ->label(__('admin/resources/clips.table.columns.absolute_votes'))
            ->tooltip(__('admin/resources/clips.table.columns.absolute_votes'))
            ->size(TextSize::Medium)
            ->icon(LucideIcon::Users)
            ->sortable()
            ->badge()
            ->color('success');
    }

    public static function impressions(string $column = 'absolute_impressions'): TextColumn
    {
        return TextColumn::make($column)
            ->label(__('admin/resources/clips.table.columns.absolute_impressions'))
            ->tooltip(__('admin/resources/clips.table.columns.absolute_impressions'))
            ->size(TextSize::Medium)
            ->icon(LucideIcon::Eye)
            ->sortable()
            ->badge()
            ->color('info');
    }

    public static function score(string $column = 'score'): TextColumn
    {
        return TextColumn::make($column)
            ->label(__('admin/resources/clips.table.columns.score'))
            ->tooltip(__('admin/resources/clips.table.columns.score'))
            ->size(TextSize::Medium)
            ->icon(LucideIcon::BarChart)
            ->sortable()
            ->badge()
            ->color('info');
    }

    public static function voteStatistics(bool $score = true, bool $jury = true, bool $public = true, bool $impressions = true, bool $absolute = false): Split
    {
        return Split::make(array_filter([
            $score ? self::score() : null,
            $jury ? self::juryVotes() : null,
            $public ? self::publicVotes() : null,
            $impressions ? self::impressions() : null,
            $absolute ? self::absoluteVotes() : null,
        ]));
    }

    public static function status(string $column = 'status'): TextColumn
    {
        return TextColumn::make($column)
            ->label('admin/resources/clips.table.columns.status')
            ->tooltip(__('admin/resources/clips.table.columns.status'))
            ->size(TextSize::Medium)
            ->icon(LucideIcon::Clipboard)
            ->badge()
            ->translateLabel();
    }

    public static function tags(string $column = 'tags.name'): TextColumn
    {
        return TextColumn::make($column)
            ->color('gray')
            ->badge();
    }

    public static function broadcasterName(): TextColumn
    {
        return TextColumn::make('owner.name')
            ->tooltip(__('admin/resources/clips.table.columns.broadcaster'))
            ->icon(LucideIcon::Video)
            ->color('gray');
    }

    public static function creatorName(): TextColumn
    {
        return TextColumn::make('creator.name')
            ->tooltip(__('admin/resources/clips.table.columns.creator'))
            ->icon(LucideIcon::Scissors)
            ->color('gray');
    }

    public static function submitterName(): TextColumn
    {
        return TextColumn::make('submitter.name')
            ->tooltip(__('admin/resources/clips.table.columns.submitter'))
            ->icon(LucideIcon::User)
            ->color('gray');
    }

    public static function createdAt(string $column = 'date'): TextColumn
    {
        return TextColumn::make($column)
            ->label(__('admin/resources/clips.table.columns.created_at'))
            ->tooltip(__('admin/resources/clips.table.columns.created_at'))
            ->icon(LucideIcon::Calendar)
            ->dateTime()
            ->sortable()
            ->color('gray');
    }

    public static function submittedAt(string $column = 'created_at'): TextColumn
    {
        return TextColumn::make($column)
            ->label(__('admin/resources/clips.table.columns.submitted_at'))
            ->tooltip(__('admin/resources/clips.table.columns.submitted_at'))
            ->icon(LucideIcon::Calendar)
            ->dateTime()
            ->sortable()
            ->color('gray');
    }

    public static function categoryName(): TextColumn
    {
        return TextColumn::make('category.title')
            ->label('admin/resources/clips.table.columns.category')
            ->translateLabel()
            ->weight('medium')
            ->wrap()
            ->color('gray')
            ->searchable();
    }

    public static function categoryImage(): ImageColumn
    {
        return ImageColumn::make('category.box_art')
            ->imageHeight(40)
            ->alignCenter()
            ->getStateUsing(fn (Clip $record) => $record->category?->getBoxArt())
            ->extraImgAttributes([
                'class' => 'object-cover rounded-md aspect-[3/4]',
            ])
            ->grow(false);
    }

    public static function category(): Split
    {
        return Split::make([
            self::categoryImage(),
            self::categoryName(),
        ])->grow(false);
    }
}
