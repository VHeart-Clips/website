<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\FaqEntries;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\FaqEntries\Pages\ListFaqEntries;
use App\Filament\AdminPanel\Resources\FaqEntries\Schemas\FaqEntryForm;
use App\Filament\AdminPanel\Resources\FaqEntries\Schemas\FaqEntryInfolist;
use App\Filament\AdminPanel\Resources\FaqEntries\Tables\FaqEntriesTable;
use App\Filament\AdminPanel\SharedRelationManagers\AuditsRelationManager;
use App\Models\Faq\FaqEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

class FaqEntryResource extends Resource
{
    use Translatable;

    protected static ?string $model = FaqEntry::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Management;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Newspaper;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return FaqEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FaqEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AuditsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqEntries::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
