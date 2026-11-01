<?php

declare(strict_types=1);

namespace App\Jobs\Compilations;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Compilation\CompilationProcessingStatus;
use App\Models\Clip;
use App\Models\Clip\Compilation;
use Carbon\CarbonInterface;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Tries(0)]
#[Backoff(60)]
#[DebounceFor(5)]
#[WithoutRelations]
#[MaxExceptions(3)]
#[DeleteWhenMissingModels]
class CompilationUpdateClipFilesJob implements ShouldQueueAfterCommit
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Compilation $compilation
    ) {}

    public function debounceId(): string
    {
        return 'CompilationUpdateClipFilesJob:'.$this->compilation->getKey();
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->debounceId())
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
    }

    public function retryUntil(): CarbonInterface
    {
        return now()->addMinutes(15);
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $compilation = $this->compilation;
        $compilationId = $compilation->getKey();
        $disk = $compilation->getDisk();
        $runningBatch = Bus::findBatch($compilation->processing_batch_id ?? '');

        if ($runningBatch !== null && ! $runningBatch->finished()) {
            Log::warning('Cancelling already running batch', [
                'batch_id' => $runningBatch->id,
            ]);

            $runningBatch->cancel();
        }

        $compilation->update([
            'processing_status' => CompilationProcessingStatus::Processing,
            'processing_batch_id' => null,
        ]);

        $finishedClips = $compilation->clips()
            ->wherePivot('claim_status', CompilationClipClaimStatus::Completed)
            ->wherePivotNotNull('file_path')
            ->get();

        Log::debug("Total {$finishedClips->count()} finished clips in compilation $compilationId");

        $keepIds = $finishedClips->pluck('id')->flip();
        $orphans = collect($disk->files('clips'))
            ->reject(static fn (string $path): bool => $keepIds->has(basename($path, '.mp4')));

        Log::debug("Deleting {$orphans->count()} orphaned files", [
            'orphans' => $orphans->all(),
        ]);

        $disk->delete($orphans->all());

        $jobs = $finishedClips
            ->map(static fn (Clip $clip): array => [
                new CompilationSynchroniseClipFileJob($compilation, $clip),
                new CompilationRefreshClipFileDataJob($compilation, $clip),
            ]);

        if ($jobs->isEmpty()) {
            dispatch(new CompilationBuildZipJob($compilation, null));

            return;
        }

        Bus::batch($jobs->all())
            ->before(static function (Batch $batch) use ($compilationId): void {
                Compilation::query()
                    ->whereKey($compilationId)
                    ->update(['processing_batch_id' => $batch->id]);
            })
            ->then(static function (Batch $batch) use ($compilationId): void {
                $compilation = Compilation::query()->find($compilationId);

                if ($compilation instanceof Compilation) {
                    dispatch(new CompilationBuildZipJob($compilation, $batch->id));
                }
            })
            ->finally(static function (Batch $batch) use ($compilationId): void {
                if (! $batch->hasFailures()) {
                    return;
                }

                Compilation::query()
                    ->whereKey($compilationId)
                    ->where('processing_batch_id', $batch->id)
                    ->update([
                        'processing_status' => CompilationProcessingStatus::Failed,
                        'processing_batch_id' => null,
                        'zip_path' => null,
                        'zip_size' => null,
                        'zip_created_at' => null,

                    ]);
            })
            ->dispatch();
    }

    public function failed(?Throwable $e): void
    {
        report($e);

        $this->compilation->update([
            'processing_status' => CompilationProcessingStatus::Failed,
            'processing_batch_id' => null,
            'zip_path' => null,
            'zip_size' => null,
            'zip_created_at' => null,
        ]);
    }
}
