<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Pages;

use App\Enums\Permission;
use App\Filament\Resources\Clips\ClipResource;
use App\Models\Clip;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ViewClip extends ViewRecord
{
    protected static string $resource = ClipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make()
                ->mentionables(fn (Model $record) => User::query()->whereHas('roles')->get())
                ->hidden(fn (): bool => ! auth()->user()->can(Permission::ViewAnyComment))
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),
            Action::make('open_twitch')
                ->label(__('admin/resources/clips.actions.view_on_twitch'))
                ->icon(Heroicon::Link)
                ->url(fn (Clip $clip): string => $clip->getClipUrl())
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
