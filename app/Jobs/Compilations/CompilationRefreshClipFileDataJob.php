<?php

declare(strict_types=1);

namespace App\Jobs\Compilations;

use App\Models\Clip;
use App\Models\Clip\Compilation;
use Carbon\CarbonInterface;
use Illuminate\Bus\Batchable;
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

#[Tries(0)]
#[Backoff(60)]
#[Timeout(60)]
#[MaxExceptions(3)]
#[WithoutRelations]
#[DeleteWhenMissingModels]
class CompilationRefreshClipFileDataJob implements ShouldQueueAfterCommit
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
            new WithoutOverlapping('CompilationRefreshClipFileDataJob:'.$this->compilation->getKey().':'.$this->clip->getKey())
                ->releaseAfter(30)
                ->expireAfter(90),
        ];
    }

    public function retryUntil(): CarbonInterface
    {
        return now()->addMinutes(30);
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $localDisk = $this->compilation->getDisk();
        $localPath = 'clips/'.$this->clip->getKey().'.mp4';

        if (! $localDisk->exists($localPath)) {
            Log::warning('Local file is missing for CompilationClip', [
                'compilation_id' => $this->compilation->getKey(),
                'clip_id' => $this->clip->getKey(),
                'local_path' => $localPath,
            ]);

            return;
        }

        // TODO: duration and other data via ffprobe
        $this->compilation->clips()->updateExistingPivot($this->clip->getKey(), [
            'file_size' => $localDisk->size($localPath),
        ]);
    }
}
