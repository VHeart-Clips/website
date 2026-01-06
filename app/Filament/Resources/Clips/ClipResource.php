<?php

namespace App\Filament\Resources\Clips;

use App\Filament\Resources\Clips\Pages\CreateClip;
use App\Filament\Resources\Clips\Pages\EditClip;
use App\Filament\Resources\Clips\Pages\ListClips;
use App\Filament\Resources\Clips\Pages\ViewClip;
use App\Filament\Resources\Clips\Schemas\ClipForm;
use App\Filament\Resources\Clips\Schemas\ClipInfolist;
use App\Filament\Resources\Clips\Tables\ClipsTable;
use App\Models\Clip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClipResource extends Resource
{
    protected static ?string $model = Clip::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClips::route('/'),
            //'create' => CreateClip::route('/create'),
            //'view' => ViewClip::route('/{record}'),
            'edit' => EditClip::route('/{record}/edit'),
        ];
    }
}
