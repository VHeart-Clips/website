<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Filament\AdminPanel\Resources\Bans\Pages\ListBans;
use App\Filament\AdminPanel\Resources\Bans\Pages\ViewBan;
use App\Filament\AdminPanel\Resources\Bans\Schemas\BanForm;
use App\Filament\AdminPanel\Resources\Bans\Schemas\BanInfolist;
use App\Filament\AdminPanel\Resources\Bans\Tables\BansTable;
use App\Models\Ban;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BanResource extends Resource
{
    protected static ?string $model = Ban::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Ban;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Moderation;

    public static function form(Schema $schema): Schema
    {
        return BanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BansTable::configure($table);
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
            'index' => ListBans::route('/'),
            'view' => ViewBan::route('/{record}'),
        ];
    }
}
