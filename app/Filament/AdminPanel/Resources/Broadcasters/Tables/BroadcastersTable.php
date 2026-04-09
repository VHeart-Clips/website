<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Broadcasters\Tables;

use App\Enums\Broadcaster\BroadcasterConsent;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class BroadcastersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('user', fn (Builder $q) => $q->where('name', 'ilike', "%{$search}%"))),

                TextColumn::make('consent')
                    ->formatStateUsing(fn (BroadcasterConsent $state): Htmlable|string|null => $state->getLabel())
                    ->color('gray')
                    ->separator()
                    ->badge(),

                IconColumn::make('submit_user_allowed')
                    ->label('Everyone can Submit')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('submit_vip_allowed')
                    ->label('VIPs can Submit')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('submit_mods_allowed')
                    ->label('Mods can Submit')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('onboarded_at')
                    ->placeholder('Not Onboarded')
                    ->sortable()
                    ->date(),
            ])
            ->filters([
                self::makeConsentFilter(),
                self::makeOnboardedFilter(),
                self::makeSubmitPermissionsFilter(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords(),
                    ForceDeleteBulkAction::make()->authorizeIndividualRecords(),
                    RestoreBulkAction::make()->authorizeIndividualRecords(),
                ]),
            ]);
    }

    private static function makeConsentFilter(): TernaryFilter
    {
        return TernaryFilter::make('consent')
            ->label('Consent')
            ->placeholder('All')
            ->trueLabel('Gave Consent')
            ->falseLabel('No Consent')
            ->queries(
                true: fn (Builder $q) => $q->whereGaveConsent(),
                false: fn (Builder $q) => $q->whereGaveNoConsent(),
            );
    }

    private static function makeSubmitPermissionsFilter(): Filter
    {
        return Filter::make('submit_permissions')
            ->label('Submission Permissions')
            ->schema([
                Fieldset::make('Submission Permissions')
                    ->columns(2)
                    ->schema([
                        Checkbox::make('submit_user_allowed')->label('Users'),
                        Checkbox::make('submit_vip_allowed')->label('VIPs'),
                        Checkbox::make('submit_mods_allowed')->label('Mods'),
                        Checkbox::make('none')->label('No One'),
                    ]),
            ])
            ->query(function (Builder $query, array $data): Builder {
                if ($data['none']) {
                    return $query
                        ->where('submit_user_allowed', false)
                        ->where('submit_vip_allowed', false)
                        ->where('submit_mods_allowed', false);
                }

                foreach (['submit_user_allowed', 'submit_vip_allowed', 'submit_mods_allowed'] as $col) {
                    if ($data[$col]) {
                        $query->where($col, true);
                    }
                }

                return $query;
            })
            ->indicateUsing(function (array $data): array {
                if ($data['none']) {
                    return [Indicator::make('Can Submit: No One')->removeField('none')];
                }

                $map = [
                    'submit_user_allowed' => 'Users',
                    'submit_vip_allowed' => 'VIPs',
                    'submit_mods_allowed' => 'Mods',
                ];

                return collect($map)
                    ->filter(fn ($_, $col): bool => $data[$col] ?? false)
                    ->map(fn ($label, string|Closure|null $col): Indicator => Indicator::make("Can Submit: $label")->removeField($col))
                    ->values()
                    ->all();
            });
    }

    private static function makeOnboardedFilter(): TernaryFilter
    {
        return TernaryFilter::make('onboarded')
            ->nullable()
            ->attribute('onboarded_at')
            ->placeholder('All')
            ->trueLabel('Onboarded')
            ->falseLabel('Not Onboarded');
    }
}
