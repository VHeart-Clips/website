<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Management;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Users\Actions\UpdateUserAction;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class GenerateClipOverlayAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Generate Overlay')
            ->icon(LucideIcon::Image)
            ->color('info')
            ->modalHeading('Generate Clip Overlay')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitActionLabel('Close')
            ->modalCancelAction(false)
            ->fillForm(fn (Clip $record): array => [
                'broadcaster' => $record->broadcaster?->name ?? 'Unknown Broadcaster',
                'category' => $record->category?->title ?? 'Unknown Category',
                'clipper' => $record->creator?->name ?? '',
                'cutter' => $record->claimer?->name ?? '',
                'avatar' => $record->broadcaster?->user?->proxiedContentUrl() ?? '',
                'show_avatar' => $this->shouldEnableAvatar($record->broadcaster?->user?->avatar_url),
            ])
            ->schema([
                TextInput::make('broadcaster')
                    ->label('Broadcaster Name')
                    ->maxLength(25)
                    ->live()
                    ->hintAction(
                        UpdateUserAction::make('broadcasterUpdate')
                            ->resolveUserUsing(fn (Clip $record) => $record->broadcaster ?? $record->broadcaster_id)
                            ->shouldCreateBroadcaster()
                            ->after(function (Clip $record, Get $get, Component $livewire): void {
                                $record->load('broadcaster');
                                $livewire->mountedActions[0]['data']['broadcaster'] = $record->broadcaster?->name ?? 'Unknown Broadcaster';
                                $livewire->mountedActions[0]['data']['avatar'] = $record->broadcaster?->user?->proxiedContentUrl() ?? '';
                                $livewire->mountedActions[0]['data']['show_avatar'] = $this->shouldEnableAvatar($record->broadcaster?->user?->avatar_url);
                                $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get));
                            })
                    )
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                TextInput::make('clipper')
                    ->label('Clipper Name')
                    ->maxLength(25)
                    ->live()
                    ->hintAction(
                        UpdateUserAction::make('clipperUpdate')
                            ->resolveUserUsing(fn (Clip $record) => $record->creator ?? $record->creator_id)
                            ->after(function (Clip $record, Get $get, Component $livewire): void {
                                $record->load('creator');
                                $livewire->mountedActions[0]['data']['clipper'] = $record->creator?->name ?? '';
                                $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get));
                            })
                    )
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                TextInput::make('cutter')
                    ->label('Cutter Name')
                    ->maxLength(25)
                    ->live()
                    ->hintAction(
                        UpdateUserAction::make('cutterUpdate')
                            ->resolveUserUsing(fn (Clip $record) => $record->claimer ?? $record->claimed_by)
                            ->after(function (Clip $record, Get $get, Component $livewire): void {
                                $record->load('cutter');
                                $livewire->mountedActions[0]['data']['cutter'] = $record->cutter?->name ?? '';
                                $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get));
                            })
                    )
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                TextInput::make('category')
                    ->label('Game / Category')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                Toggle::make('show_avatar')
                    ->label('Show Avatar')
                    ->default(true)
                    ->disabled(fn (Clip $record): bool => ! $this->shouldEnableAvatar($record->broadcaster?->user?->avatar_url))
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),
            ])
            ->modalContentFooter(fn (Clip $record): View => view(
                'filament.resources.clip-resource.actions.generate-clip-overlay',
                [
                    'initialState' => [
                        'broadcaster' => $this->userName($record->broadcaster?->name, 'Unknown Broadcaster'),
                        'category' => $this->categoryName($record->category?->title ?? 'Unknown Category'),
                        'clipper' => $this->userName($record->creator?->name),
                        'cutter' => $this->userName($record->claimer?->name),
                        'avatar' => $record->broadcaster?->user?->proxiedContentUrl() ?? '',
                        'show_avatar' => $this->shouldEnableAvatar($record->broadcaster?->user?->avatar_url),
                    ],
                    'identifier' => $record->id
                        .'__'.Str::slug($record->broadcaster?->name ?? 'Unknown Broadcaster')
                        .'__'.Str::slug($record->category?->title ?? 'Unknown Category')
                        .'__'.Str::slug($record->claimer?->name ?? 'Unknown Cutter')
                        .'__'.Str::slug($record->creator?->name ?? 'Unknown Creator'),
                ]
            ))
            ->action(null);
    }

    public static function getDefaultName(): ?string
    {
        return 'generateClipOverlay';
    }

    private function buildOverlayState(Get $get): array
    {
        return [[
            'broadcaster' => $this->userName($get('broadcaster'), 'Unknown Broadcaster'),
            'category' => $this->categoryName($get('category')),
            'clipper' => $this->userName($get('clipper')),
            'cutter' => $this->userName($get('cutter')),
            'avatar' => $get('avatar'),
            'show_avatar' => $get('show_avatar'),
        ]];
    }

    private function userName(?string $value, string $default = ''): string
    {
        return Str::limit($value ?? $default, 18);
    }

    private function categoryName(?string $value): string
    {
        return Str::limit($value ?? 'Unknown Category', 39);
    }

    private function shouldEnableAvatar(?string $value): bool
    {
        return (bool) $value;
    }
}
