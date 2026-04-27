<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Clips;

use App\Enums\Filament\LucideIcon;
use App\Filament\Dashboard\Resources\Clips\Pages\EditClip;
use App\Filament\Dashboard\Resources\Clips\Pages\ListClips;
use App\Filament\Dashboard\Resources\Clips\Pages\ViewClip;
use App\Filament\Dashboard\Resources\Clips\RelationManagers\CompilationsRelationManager;
use App\Filament\Dashboard\Resources\Clips\Schemas\ClipForm;
use App\Filament\Dashboard\Resources\Clips\Schemas\ClipInfolist;
use App\Filament\Dashboard\Resources\Clips\Tables\ClipsTable;
use App\Models\Clip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClipResource extends Resource
{
    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $model = Clip::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Film;

    public static function form(Schema $schema): Schema
    {
        return ClipForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClipInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClipsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CompilationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClips::route('/'),
            'view' => ViewClip::route('/{record}'),
            'edit' => EditClip::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withAbsoluteVoteCount();
    }
}
