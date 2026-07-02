<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Compilations\Pages\CreateCompilation;
use App\Filament\AdminPanel\Resources\Compilations\Pages\EditCompilation;
use App\Filament\AdminPanel\Resources\Compilations\Pages\ListCompilations;
use App\Filament\AdminPanel\Resources\Compilations\Pages\ViewCompilation;
use App\Filament\AdminPanel\Resources\Compilations\RelationManagers\ClipsRelationManager;
use App\Filament\AdminPanel\Resources\Compilations\Schemas\CompilationForm;
use App\Filament\AdminPanel\Resources\Compilations\Schemas\CompilationInfoList;
use App\Filament\AdminPanel\Resources\Compilations\Tables\CompilationsTable;
use App\Filament\AdminPanel\SharedRelationManagers\AuditsRelationManager;
use App\Models\Clip\Compilation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompilationResource extends Resource
{
    protected static ?string $model = Compilation::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Clipboard;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CompilationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CompilationInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompilationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ClipsRelationManager::class,
            AuditsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompilations::route('/'),
            'create' => CreateCompilation::route('/create'),
            'view' => ViewCompilation::route('/{record}'),
            'edit' => EditCompilation::route('/{record}/edit'),
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
