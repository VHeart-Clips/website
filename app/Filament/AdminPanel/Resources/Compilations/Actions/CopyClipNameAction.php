<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Compilations\RelationManagers\ClipsRelationManager;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CopyClipNameAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('admin/resources/compilations.relation_managers.clips.actions.copy_filename')
            ->translateLabel()
            ->icon(LucideIcon::ClipboardList)
            ->color('gray')
            ->tooltip(__('admin/resources/compilations.relation_managers.clips.actions.copy_filename_tooltip'))
            ->action(function (Clip $clip, ClipsRelationManager $livewire): void {
                if (! $clip->owner) {
                    Notification::make()
                        ->title(__('admin/resources/compilations.relation_managers.clips.notifications.filename_copy_failed_title'))
                        ->body(__('admin/resources/compilations.relation_managers.clips.notifications.filename_copy_failed_no_broadcaster'))
                        ->danger()
                        ->send();

                    return;
                }

                $filename = Clip\CompilationClip::recommendedFileName($clip, $livewire->getOwnerRecord(), $clip->claimer);
                $livewire->js('window.navigator.clipboard.writeText('.json_encode($filename, JSON_THROW_ON_ERROR).');');

                Notification::make()
                    ->title(__('admin/resources/compilations.relation_managers.clips.notifications.filename_copied'))
                    ->body($filename)
                    ->success()
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'copyClipName';
    }
}
