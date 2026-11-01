<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use League\Flysystem\FilesystemException;

#[Signature('app:cleanup-temporary-compilation-clip-uploads {--hours=12 : Max age in hours}')]
#[Description('Cleanup temporary compilation clip uploads')]
class CleanupTemporaryCompilationClipUploadsCommand extends Command
{
    /**
     * @throws FilesystemException
     */
    public function handle(): int
    {
        $disk = Storage::disk('compilation_clips_archive');
        $files = $disk->allFiles('tmp');
        $maxAge = now()->subHours((int) $this->option('hours'));
        $this->info('Total temporary files: '.count($files));
        $this->info('Cleaning up files older than '.$maxAge->diffAsCarbonInterval());
        $totalSizeDeleted = 0;
        $totalFilesDeleted = 0;

        foreach ($files as $file) {
            $fileDate = Date::parse($disk->lastModified($file));

            if ($fileDate->lessThan($maxAge)) {
                $this->info("Deleting $file");
                $size = $disk->fileSize($file);

                if ($disk->delete($file)) {
                    $totalSizeDeleted += $size;
                    $totalFilesDeleted++;
                } else {
                    $this->warn("Failed to delete $file");
                }
            } else {
                $this->line("Skipping $file");
            }

        }

        $this->info("Removed $totalFilesDeleted files (".Number::fileSize($totalSizeDeleted).')');

        return static::SUCCESS;
    }
}
