<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Actions\Management;

use App\Enums\Filament\LucideIcon;
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
                'show_avatar' => true,
            ])
            ->schema([
                TextInput::make('broadcaster')
                    ->label('Broadcaster Name')
                    ->maxLength(25)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                TextInput::make('clipper')
                    ->label('Clipper Name')
                    ->maxLength(25)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                TextInput::make('cutter')
                    ->label('Cutter Name')
                    ->maxLength(25)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                TextInput::make('category')
                    ->label('Game / Category')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),

                Toggle::make('show_avatar')
                    ->label('Show Avatar')
                    ->default(true)
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Component $livewire) => $livewire->dispatch('clip-overlay-updated', ...$this->buildOverlayState($get))),
            ])
            ->modalContentFooter(fn (Clip $record): View => view(
                'filament.resources.clip-resource.actions.generate-clip-overlay',
                [
                    'initialState' => [
                        'broadcaster' => Str::limit($record->broadcaster?->name ?? 'Unknown Broadcaster', 18),
                        'category' => Str::limit($record->category?->title ?? 'Unknown Category', 40),
                        'clipper' => Str::limit($record->creator?->name ?? '', 18),
                        'cutter' => Str::limit($record->claimer?->name ?? '', 18),
                        'avatar' => $record->broadcaster?->user?->proxiedContentUrl() ?? '',
                        'show_avatar' => true,
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
            'broadcaster' => Str::limit($get('broadcaster'), 18),
            'category' => Str::limit($get('category'), 40),
            'clipper' => Str::limit($get('clipper'), 18),
            'cutter' => Str::limit($get('cutter'), 18),
            'avatar' => $get('avatar'),
            'show_avatar' => $get('show_avatar'),
        ]];
    }
}
