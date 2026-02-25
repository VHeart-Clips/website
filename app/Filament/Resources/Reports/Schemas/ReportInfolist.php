<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\Permission;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Group::make([
                            Section::make('Report Overview')
                                ->icon(Heroicon::DocumentText)
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('reason')
                                            ->label('Reason')
                                            ->badge()
                                            ->size(TextSize::Large)
                                            ->color('danger'),

                                        TextEntry::make('status')
                                            ->label('Current Status')
                                            ->badge()
                                            ->size(TextSize::Large)
                                            ->alignEnd(),
                                    ]),

                                    TextEntry::make('description')
                                        ->prose()
                                        ->markdown()
                                        ->label('Description')
                                        ->placeholder('No specific description provided.')
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'leading-relaxed']),
                                ]),

                            Section::make('Action')
                                ->visible(fn (Model $record): bool => $record->resolve_action !== null)
                                ->icon(Heroicon::CommandLine)
                                ->compact()
                                ->schema([
                                    TextEntry::make('resolve_action')
                                        ->label('Moderative Action'),

                                    TextEntry::make('resolve_description')
                                        ->label('Details')
                                        ->markdown()
                                        ->placeholder('Non Provided'),
                                ]),

                            Section::make('Reported Content')
                                ->headerActions([
                                    Action::make('view')
                                        ->color('info')
                                        ->icon(Heroicon::ArrowTopRightOnSquare)
                                        ->disabled(fn (Model $record): bool => self::getResourceUrl($record->reportable) === null)
                                        ->url(fn (Model $record): ?string => self::getResourceUrl($record->reportable), shouldOpenInNewTab: true),
                                ])
                                ->icon(Heroicon::Eye)
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('reportable_type')
                                        ->label('Content Type')
                                        ->badge()
                                        ->color('gray'),

                                    TextEntry::make('reportable_id')
                                        ->label('Target ID')
                                        ->fontFamily('mono')
                                        ->copyable(),
                                ]),
                        ])->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Involved Parties')
                                ->icon(Heroicon::Users)
                                ->schema([
                                    TextEntry::make('reporter.name')
                                        ->url(fn (Model $record): ?string => self::getResourceUrl($record->reporter), true)
                                        ->label('Reporter')
                                        ->icon(Heroicon::User)
                                        ->weight(FontWeight::Bold)
                                        ->color('gray'),

                                    TextEntry::make('claimer.name')
                                        ->url(fn (Model $record): ?string => self::getResourceUrl($record->claimer), true)
                                        ->label('Claimed By')
                                        ->icon(Heroicon::ShieldCheck)
                                        ->placeholder('Unclaimed')
                                        ->weight(FontWeight::Bold)
                                        ->color('gray'),

                                    TextEntry::make('resolver.name')
                                        ->url(fn (Model $record): ?string => self::getResourceUrl($record->resolver), true)
                                        ->label('Resolved By')
                                        ->icon(Heroicon::CheckBadge)
                                        ->placeholder('Unresolved')
                                        ->weight(FontWeight::Bold)
                                        ->color('gray'),
                                ]),

                            Section::make('Timeline')
                                ->icon(Heroicon::Clock)
                                ->compact()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Submitted At')
                                        ->dateTime(),

                                    TextEntry::make('claimed_at')
                                        ->label('Claimed')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('resolved_at')
                                        ->label('Resolved')
                                        ->dateTime()
                                        ->placeholder('-')
                                        ->color(fn ($state): string => $state ? 'success' : 'gray'),

                                    TextEntry::make('updated_at')
                                        ->label('Last Activity')
                                        ->since()
                                        ->color('gray'),
                                ]),
                        ])->columnSpan(['lg' => 1]),
                    ]),
                Section::make('Comments')
                    ->columnSpanFull()
                    ->hidden(fn (): bool => ! auth()->user()->can(Permission::ViewAnyComment))
                    ->schema([
                        CommentsEntry::make('comments')
                            ->hiddenLabel()
                            ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get()),
                    ]),
            ]);
    }

    private static function getResourceUrl(?Model $model): ?string
    {
        if (! $model || Filament::getModelResource($model) === null) {
            return null;
        }

        return Filament::getResourceUrl($model, 'view');
    }
}
