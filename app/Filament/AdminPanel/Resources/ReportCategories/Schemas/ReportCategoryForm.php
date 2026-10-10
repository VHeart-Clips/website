<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\ReportCategories\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Enums\Reports\ReportCategoryDetailsType;
use App\Enums\Reports\ReportCategoryReportableType;
use App\Models\ReportCategory;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class ReportCategoryForm
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
                                TextInput::make('name')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('summary')
                                    ->columnSpanFull(),
                                MarkdownEditor::make('description')
                                    ->columnSpanFull(),
                            ]),

                        Group::make()
                            ->schema([
                                Section::make('Behavior')
                                    ->compact()
                                    ->icon(LucideIcon::Workflow)
                                    ->schema([
                                        Toggle::make('is_note')
                                            ->label('Info only')
                                            ->helperText('Disallows submission for this reason, useful to guide them elsewhere')
                                            ->default(false)
                                            ->live(),

                                        Select::make('details_type')
                                            ->label('Additional details')
                                            ->options(ReportCategoryDetailsType::class)
                                            ->required()
                                            ->visible(fn (Get $get): bool => ! $get('is_note')),
                                    ]),

                                Section::make('Scope')
                                    ->compact()
                                    ->icon(LucideIcon::ListFilter)
                                    ->schema([
                                        Select::make('parent_id')
                                            ->label('Parent category')
                                            ->relationship(
                                                name: 'parentCategory',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query, ?ReportCategory $record) => $query
                                                    // for now we dont allow deep parenting (if you can call it that)
                                                    // as this would expose us to a ton of recursive issues lol
                                                    ->whereNull('parent_id')
                                                    ->limit(5)
                                                    ->orderBy('sort_order')
                                                    ->orderBy('id'),
                                                ignoreRecord: true
                                            )
                                            ->disabled(fn (?ReportCategory $record, Get $get): bool => $record?->subCategories()->withTrashed()->exists() && empty($get('parent_id')))
                                            ->rules([
                                                fn (?ReportCategory $record): Closure => static function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                                    if ($record?->subCategories()->withTrashed()->exists()) {
                                                        $fail('A category with subcategories cannot have a parent.');
                                                    }
                                                },
                                            ])
                                            ->hintAction(
                                                Action::make('fixDeadlock')
                                                    ->label('Fix Deadlock')
                                                    ->tooltip('Somehow we created a deadlock where 2 categories are the parent of each other, let me fix that for you lol')
                                                    ->icon(LucideIcon::Wrench)
                                                    ->color('danger')
                                                    ->requiresConfirmation()
                                                    ->visible(fn (ReportCategory $record): bool => $record->parent_id !== null
                                                        && $record->parentCategory()->withTrashed()->value('parent_id') === $record->getKey())
                                                    ->action(function (ReportCategory $record, Set $set): void {
                                                        DB::transaction(function () use ($record): void {
                                                            ReportCategory::query()
                                                                ->withTrashed()
                                                                ->whereKey([$record->getKey(), $record->parent_id])
                                                                ->lockForUpdate()
                                                                ->get();

                                                            ReportCategory::query()
                                                                ->withTrashed()
                                                                ->whereKey([$record->getKey(), $record->parent_id])
                                                                ->update(['parent_id' => null]);
                                                        });

                                                        $set('parent_id', null);
                                                    }),
                                            )
                                            ->searchable()
                                            ->preload(),

                                        CheckboxList::make('reportable_types')
                                            ->options(ReportCategoryReportableType::class)
                                            ->helperText('Limits this category to specific types of reportable content, leave unchecked to allow everything')
                                            ->columns(2),

                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->nullable(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
