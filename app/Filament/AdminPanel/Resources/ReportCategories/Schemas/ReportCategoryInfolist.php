<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Filament\Actions\ResourceLinkAction;
use App\Models\ReportCategory;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ReportCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Content')
                            ->compact()
                            ->columnSpan(2)
                            ->icon(LucideIcon::NotepadText)
                            ->schema([
                                TextEntry::make('name')
                                    ->hiddenLabel()
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('summary')
                                    ->placeholder('None'),
                                TextEntry::make('description')
                                    ->placeholder('None')
                                    ->markdown(),
                            ]),

                        Group::make()
                            ->schema([
                                Section::make('Behavior')
                                    ->compact()
                                    ->icon(LucideIcon::Workflow)
                                    ->schema([
                                        IconEntry::make('is_note')
                                            ->label('Info only')
                                            ->boolean(),
                                        TextEntry::make('details_type')
                                            ->label('Additional details')
                                            ->badge()
                                            ->visible(fn (ReportCategory $record): bool => ! $record->is_note),
                                    ]),

                                Section::make('Scope')
                                    ->compact()
                                    ->icon(LucideIcon::ListFilter)
                                    ->schema([
                                        TextEntry::make('parentCategory.name')
                                            ->label('Parent category')
                                            ->hintAction(
                                                ResourceLinkAction::make('viewParentCategory')
                                                    ->openUrlInNewTab()
                                                    ->relationship('parentCategory')
                                            )
                                            ->placeholder('None'),
                                        TextEntry::make('reportable_types')
                                            ->badge()
                                            ->placeholder('All types'),
                                        TextEntry::make('sort_order')
                                            ->numeric(),
                                    ]),
                            ]),
                    ]),

                Section::make('Metadata')
                    ->icon(LucideIcon::Clock)
                    ->compact()
                    ->collapsed()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (ReportCategory $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
