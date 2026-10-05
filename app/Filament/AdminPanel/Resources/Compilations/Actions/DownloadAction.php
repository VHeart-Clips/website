<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Resources\Compilations\Actions;

use App\Enums\Filament\LucideIcon;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class DownloadAction extends Action
{
    protected string|Closure|null $filePath = null;

    protected string|Closure $fileDisk = 's3';

    protected string|Closure|null $fileName = null;

    protected bool|Closure $shouldAppendExtension = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Download')
            ->icon(LucideIcon::Download)
            ->color('info')
            ->rateLimit(5)
            ->action(function (Component $livewire): void {
                $path = $this->getPath();
                $disk = Storage::disk($this->getDisk());

                if ($path === null || ! $disk->exists($path)) {
                    Log::debug('File not found', [
                        'disk' => $this->getDisk(),
                        'path' => $path,
                    ]);

                    Notification::make()->title('File not found')->danger()->send();

                    return;
                }

                $livewire->redirect($disk->temporaryUrl($path, now()->addMinutes(15), [
                    'ResponseContentDisposition' => 'attachment; filename="'.addcslashes($this->getFileName($path), '"\\').'"',
                ]));
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'download';
    }

    public function path(string|Closure|null $path): static
    {
        $this->filePath = $path;

        return $this;
    }

    public function disk(string|Closure $disk): static
    {
        $this->fileDisk = $disk;

        return $this;
    }

    public function getFileNameUsing(string|Closure|null $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function appendExtension(bool|Closure $condition = true): static
    {
        $this->shouldAppendExtension = $condition;

        return $this;
    }

    public function getFileName(string $path): string
    {
        $name = $this->evaluate($this->fileName, ['path' => $path]);

        if (! is_string($name) || blank($name)) {
            return basename($path);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension !== '' && $this->evaluate($this->shouldAppendExtension) && ! str_ends_with(mb_strtolower($name), '.'.mb_strtolower($extension))) {
            $name .= '.'.$extension;
        }

        return $name;
    }

    public function getPath(): ?string
    {
        $path = $this->evaluate($this->filePath) ?? $this->getRecord()?->getAttribute($this->getName());

        return is_string($path) && filled($path) ? $path : null;
    }

    public function getDisk(): string
    {
        return $this->evaluate($this->fileDisk);
    }
}
