<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Bans\Schemas;

use App\Enums\Filament\LucideIcon;
use App\Enums\Permission;
use App\Filament\Infolists\Components\MorphEntry;
use App\Models\Ban;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;

class BanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Ban Details')
                            ->icon(LucideIcon::ShieldBan)
                            ->columnSpan(2)
                            ->schema([
                                MorphEntry::make('bannable')
                                    ->label('Banned Entity'),

                                TextEntry::make('reason')
                                    ->placeholder('No reason provided')
                                    ->columnSpanFull()
                                    ->markdown(),
                            ]),

                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Banned By')
                                    ->icon(LucideIcon::User)
                                    ->compact()
                                    ->schema([
                                        MorphEntry::make('bannedBy'),
                                    ]),

                                Section::make('Unbanned By')
                                    ->icon(LucideIcon::LockOpen)
                                    ->visible(fn (Ban $record) => $record?->unbanned_at)
                                    ->compact()
                                    ->schema([
                                        MorphEntry::make('unbannedBy')
                                            ->hiddenLabel(),
                                    ]),

                                Section::make('Timestamps')
                                    ->compact()
                                    ->icon(LucideIcon::Calendar)
                                    ->schema([
                                        TextEntry::make('unbanned_at')
                                            ->visible(fn (Ban $record) => $record?->unbanned_at)
                                            ->label('Unbanned')
                                            ->dateTimeTooltip()
                                            ->dateTime()
                                            ->since()
                                            ->placeholder('-'),

                                        TextEntry::make('banned_until')
                                            ->label('Expires')
                                            ->dateTimeTooltip()
                                            ->dateTime()
                                            ->since()
                                            ->placeholder('Permanent'),

                                        TextEntry::make('created_at')
                                            ->label('Created')
                                            ->dateTimeTooltip()
                                            ->dateTime()
                                            ->since(),

                                        TextEntry::make('updated_at')
                                            ->label('Updated')
                                            ->dateTimeTooltip()
                                            ->dateTime()
                                            ->since(),
                                    ]),
                            ]),
                    ]),

                CommentsEntry::make('discussion')
                    ->mentionables(fn (Model $record) => User::query()
                        ->whereHas('roles', fn (Builder $q): Builder => $q
                            ->whereHas(
                                'permissions', fn (Builder $q): Builder => $q
                                    ->where('permission', Permission::ViewAnyBan)
                            )
                            ->orWhere('id', 0)
                        )->get()
                    )
                    ->columnSpanFull(),
            ]);
    }
}
