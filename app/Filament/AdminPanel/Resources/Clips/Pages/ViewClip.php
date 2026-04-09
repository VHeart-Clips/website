<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Clips\Pages;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Clips\Actions\Management\ClipFeedbackAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\FlagClipAction;
use App\Filament\AdminPanel\Resources\Clips\Actions\Moderation\UnflagClipAction;
use App\Filament\AdminPanel\Resources\Clips\ClipResource;
use App\Models\Clip;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
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
                ->authorize('comment')
                ->perPage(4)
                ->loadMoreIncrementsBy(8)
                ->modalWidth(Width::SevenExtraLarge),
            Action::make('open_twitch')
                ->label(__('admin/resources/clips.actions.view_on_twitch'))
                ->icon(LucideIcon::ExternalLink)
                ->url(fn (Clip $clip): string => $clip->getClipUrl())
                ->openUrlInNewTab(),
            ClipFeedbackAction::make(),
            FlagClipAction::make(),
            UnflagClipAction::make(),
            EditAction::make(),
        ];
    }
}
