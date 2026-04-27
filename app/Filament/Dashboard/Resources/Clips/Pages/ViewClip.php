<?php

declare(strict_types=1);

namespace App\Filament\Dashboard\Resources\Clips\Pages;

use App\Enums\Filament\LucideIcon;
use App\Filament\Dashboard\Resources\Clips\ClipResource;
use App\Filament\Resources\Clips\ClipActions;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClip extends ViewRecord
{
    protected static string $resource = ClipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_twitch')
                ->label(__('admin/resources/clips.actions.view_on_twitch'))
                ->icon(LucideIcon::Link)
                ->url(fn (Clip $clip): string => $clip->getClipUrl())
                ->openUrlInNewTab(),
            EditAction::make(),
            ClipActions::reportableActionGroup(),
        ];
    }
}
