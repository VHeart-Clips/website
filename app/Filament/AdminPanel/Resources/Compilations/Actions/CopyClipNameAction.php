<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Filament\LucideIcon;
use App\Filament\AdminPanel\Resources\Compilations\RelationManagers\ClipsRelationManager;
use App\Models\Clip;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

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

                $broadcaster = $clip->owner->name;
                $cutter = $clip->claimer?->name ?? 'Unknown Cutter';
                $clipper = $clip->creator?->name ?? 'Unknown Clipper';
                $category = $clip->category->title;
                $episode = $livewire->getOwnerRecord()?->title ?? 'Unknown Episode';

                $filename = $this->sanitizeFilename("{$clip->id}__{$broadcaster}__{$category}__{$cutter}__{$clipper}__{$episode}.mp4");
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

    // https://stackoverflow.com/a/42058764 by mgutt. License - CC BY-SA 4.0
    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace(
            pattern: '~
                [<>:"/\\\|?*]|        # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
                [\x00-\x1F]|          # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
                [\x7F\xA0\xAD]|       # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
                [#\[\]@!$&\'()+,;=]|  # URI reserved https://www.rfc-editor.org/rfc/rfc3986#section-2.2
                [{}^\~`]              # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
            ~x',
            replacement: '-',
            subject: $filename
        );
        $filename = Str::ltrim($filename, '.-');
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $maxLength = 255 - ($ext !== '' && $ext !== '0' ? Str::length($ext) + 1 : 0);

        return mb_strcut(
            string: $name,
            start: 0,
            length: $maxLength,
            encoding: mb_detect_encoding($filename) ?: 'UTF-8'
        ).($ext !== '' && $ext !== '0' ? '.'.$ext : '');
    }
}
