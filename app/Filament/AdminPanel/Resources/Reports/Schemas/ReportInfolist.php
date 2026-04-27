<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Reports\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Filament\Actions\ResourceLinkAction;
use App\Filament\Infolists\Components\MorphEntry;
use App\Models\Report;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
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
                                ->icon(LucideIcon::Text)
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
                                ->icon(LucideIcon::Book)
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
                                    ResourceLinkAction::make('view_reportable')
                                        ->relationship('reportable')
                                        ->openUrlInNewTab()
                                        ->color('info'),
                                ])
                                ->icon(LucideIcon::Eye)
                                ->columns(2)
                                ->schema([
                                    MorphEntry::make('reportable')
                                        ->placeholder('Removed')
                                        ->columnSpanFull(),
                                ]),
                        ])->columnSpan(['lg' => 2]),

                        Group::make([
                            Section::make('Involved Parties')
                                ->icon(LucideIcon::Users)
                                ->schema([
                                    TextEntry::make('reporter.name')
                                        ->hintAction(
                                            ResourceLinkAction::make('reporterLink')
                                                ->openUrlInNewTab()
                                                ->relationship('reporter')
                                        )
                                        ->label('Reporter')
                                        ->icon(LucideIcon::User)
                                        ->weight(FontWeight::Bold)
                                        ->color('gray'),

                                    TextEntry::make('claimer.name')
                                        ->hintAction(
                                            ResourceLinkAction::make('claimerLink')
                                                ->openUrlInNewTab()
                                                ->relationship('claimer')
                                        )
                                        ->label('Claimed By')
                                        ->icon(LucideIcon::ShieldCheck)
                                        ->placeholder('Unclaimed')
                                        ->weight(FontWeight::Bold)
                                        ->color('gray'),

                                    TextEntry::make('resolver.name')
                                        ->hintAction(
                                            ResourceLinkAction::make('resolverLink')
                                                ->openUrlInNewTab()
                                                ->relationship('resolver')
                                        )
                                        ->label('Resolved By')
                                        ->icon(LucideIcon::BadgeCheck)
                                        ->placeholder('Unresolved')
                                        ->weight(FontWeight::Bold)
                                        ->color('gray'),
                                ]),

                            Section::make('Timeline')
                                ->icon(LucideIcon::Clock)
                                ->compact()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Submitted At')
                                        ->dateTime(),

                                    TextEntry::make('claimed_at')
                                        ->label('Claimed')
                                        ->dateTime()
                                        ->placeholder('-')
                                        ->hint(fn ($record) => $record->claimed_at
                                            ? $record->created_at->diffForHumans($record->claimed_at, true)
                                            : $record->created_at->diffForHumans(now(), true)
                                        )
                                        ->hintColor('gray')
                                        ->hintIcon(fn (Report $record): LucideIcon => $record->claimed_at ? LucideIcon::Clock : LucideIcon::Timer),

                                    TextEntry::make('resolved_at')
                                        ->label('Resolved')
                                        ->dateTime()
                                        ->placeholder('-')
                                        ->color(fn ($state): string => $state ? 'success' : 'gray')
                                        ->hint(fn ($record) => $record->resolved_at
                                            ? $record->created_at->diffForHumans($record->resolved_at, true)
                                            : $record->created_at->diffForHumans(now(), true)
                                        )
                                        ->hintColor('gray')
                                        ->hintIcon(fn (Report $record): LucideIcon => $record->resolved_at ? LucideIcon::Clock : LucideIcon::Timer),

                                    TextEntry::make('updated_at')
                                        ->label('Last Activity')
                                        ->since()
                                        ->color('gray'),
                                ]),
                        ])->columnSpan(['lg' => 1]),
                    ]),
                Section::make('Comments')
                    ->columnSpanFull()
                    ->hidden(fn (Report $record): bool => ! auth()->user()->can('comment', $record))
                    ->schema([
                        CommentsEntry::make('comments')
                            ->hiddenLabel()
                            ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get()),
                    ]),
            ]);
    }
}
