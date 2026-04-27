<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Reports;

use App\Enums\Filament\LucideIcon;
use App\Enums\NavigationGroup;
use App\Enums\Reports\ReportStatus;
use App\Filament\AdminPanel\Resources\Reports\Pages\ListReports;
use App\Filament\AdminPanel\Resources\Reports\Pages\ViewReport;
use App\Filament\AdminPanel\Resources\Reports\Schemas\ReportInfolist;
use App\Filament\AdminPanel\Resources\Reports\Tables\ReportsTable;
use App\Models\Report;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Moderation;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Flag;

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = null;

    public static function getNavigationBadge(): ?string
    {
        $count = static::$model::query()
            ->where('status', ReportStatus::Pending)
            ->whereNull('claimed_by')
            ->whereNull('resolved_by')
            ->count();

        if ($count > 0) {
            return (string) $count;
        }

        return null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'view' => ViewReport::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with([
                'reportable' => fn ($q) => $q->withTrashed(),
                'reporter' => fn ($q) => $q->withTrashed(),
            ]);
    }
}
