<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Filament\LucideIcon;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Js;
use Livewire\Component;

class DownloadAllAction extends Action
{
    protected array|Closure $files = [];

    protected string|Closure $fileDisk = 's3';

    protected int $delay = 400;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Download all')
            ->icon(LucideIcon::Download)
            ->color('info')
            ->rateLimit(2)
            ->action(function (Component $livewire): void {
                $files = $this->evaluate($this->files);

                if ($files === []) {
                    Notification::make()
                        ->title('No files to download')
                        ->danger()
                        ->send();

                    $this->halt();
                }

                $disk = Storage::disk($this->evaluate($this->fileDisk));
                $urls = [];

                foreach ($files as $path => $name) {
                    $urls[] = $disk->temporaryUrl($path, now()->addMinutes(10), [
                        'ResponseContentDisposition' => 'attachment; filename="'.addcslashes($name, '"\\').'"',
                    ]);
                }

                Notification::make()
                    ->title('Downloading Files')
                    ->body('If nothing happens, allow multiple downloads in your browser settings or address bar.')
                    ->info()
                    ->send();

                $livewire->js(sprintf(<<<'JS'
                    %s.forEach((url, i) => setTimeout(() => {
                        const a = document.createElement('a');
                        a.href = url;
                        a.click();
                        a.remove();
                    }, i * %d));
                JS, Js::from($urls), $this->delay));
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'downloadAll';
    }

    public function getFilesUsing(array|Closure $files): static
    {
        $this->files = $files;

        return $this;
    }

    public function disk(string|Closure $disk): static
    {
        $this->fileDisk = $disk;

        return $this;
    }

    public function delay(int $milliseconds): static
    {
        $this->delay = $milliseconds;

        return $this;
    }
}
