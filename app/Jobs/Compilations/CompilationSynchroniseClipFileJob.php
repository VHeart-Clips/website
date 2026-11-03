<?php

declare(strict_types=1);

namespace App\Jobs\Compilations;

use App\Models\Clip;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use Carbon\CarbonInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Tries(0)]
#[MaxExceptions(3)]
#[Backoff(60)]
#[Timeout(600)]
#[WithoutRelations]
#[DeleteWhenMissingModels]
class CompilationSynchroniseClipFileJob implements ShouldQueueAfterCommit
{
    use Batchable;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Compilation $compilation,
        public Clip $clip,
    ) {}

    public function middleware(): array
    {
        return [
            new WithoutOverlapping('CompilationSynchroniseClipFileJob:'.$this->compilation->getKey().':'.$this->clip->getKey())
                ->expireAfter(600)
                ->releaseAfter(30),
        ];
    }

    public function retryUntil(): CarbonInterface
    {
        return now()->addMinutes(5);
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $compilationClip = CompilationClip::query()
            ->where('compilation_id', $this->compilation->getKey())
            ->where('clip_id', $this->clip->getKey())
            ->first();

        $path = $compilationClip?->file_path;

        if (blank($path)) {
            Log::debug('CompilationClip does not have a file attached', [
                'compilation_id' => $this->compilation->getKey(),
                'clip_id' => $this->clip->getKey(),
            ]);

            return;
        }

        $localDisk = $this->compilation->getDisk();
        $localPath = 'clips/'.$this->clip->getKey().'.mp4';
        $remoteDisk = Storage::disk('compilation_clips_archive');

        if (! $remoteDisk->exists($path)) {
            Log::warning('Tried downloading missing CompilationClip file', [
                'compilation_id' => $this->compilation->getKey(),
                'clip_id' => $this->clip->getKey(),
                'path' => $path,
                'local_path' => $localPath,
            ]);

            return;
        }

        if ($this->isUpToDate($localDisk, $localPath, $remoteDisk, $path)) {
            return;
        }

        Log::debug("Downloading Clip $compilationClip->clip_id for Compilation $compilationClip->compilation_id...", [
            'compilation_id' => $this->compilation->getKey(),
            'clip_id' => $this->clip->getKey(),
            'path' => $path,
            'local_path' => $localPath,
        ]);

        $tmpPath = "$localPath.part";
        $stream = $remoteDisk->readStream($path)
            ?? throw new RuntimeException("Unable to read remote file $path");

        try {
            $localDisk->writeStream($tmpPath, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $localDisk->move($tmpPath, $localPath);
    }

    private function isUpToDate(Filesystem $local, string $localPath, Filesystem $remote, string $remotePath): bool
    {
        return $local->exists($localPath)
            && $local->size($localPath) === $remote->size($remotePath)
            && $local->lastModified($localPath) >= $remote->lastModified($remotePath);
    }
}
