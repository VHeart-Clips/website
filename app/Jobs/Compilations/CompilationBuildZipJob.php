<?php

declare(strict_types=1);

namespace App\Jobs\Compilations;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Enums\Compilation\CompilationProcessingStatus;
use App\Models\Clip\Compilation;
use App\Models\Clip\CompilationClip;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\File;
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
use Illuminate\Support\Number;
use RuntimeException;
use Throwable;
use ZipArchive;

#[Tries(0)]
#[MaxExceptions(2)]
#[Backoff(120)]
#[Timeout(3600)]
#[WithoutRelations]
#[DeleteWhenMissingModels]
class CompilationBuildZipJob implements ShouldQueueAfterCommit
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Compilation $compilation,
        public ?string $batchId,
    ) {}

    public function middleware(): array
    {
        return [
            new WithoutOverlapping('CompilationBuildZipJob:'.$this->compilation->getKey())
                ->expireAfter(1200)
                ->releaseAfter(60),
        ];
    }

    public function retryUntil(): CarbonInterface
    {
        return now()->addMinutes(15);
    }

    public function handle(): void
    {
        if (! $this->scopedQuery()->exists()) {
            return;
        }

        $disk = $this->compilation->getDisk();
        $remote = Storage::disk('compilation_clips_archive');
        $remoteDir = (string) $this->compilation->getKey();
        $fileName = 'compilation.zip';

        // clip id => zip entry name
        $entries = CompilationClip::query()
            ->where('compilation_id', $this->compilation->getKey())
            ->where('claim_status', CompilationClipClaimStatus::Completed)
            ->whereNotNull('file_path')
            ->with(['clip.owner', 'clip.creator', 'clip.category', 'claimer'])
            ->get()
            ->filter(fn (CompilationClip $pivot): bool => $disk->exists("clips/$pivot->clip_id.mp4"))
            ->mapWithKeys(fn (CompilationClip $pivot): array => [
                $pivot->clip_id => CompilationClip::recommendedFileName($pivot->clip, $this->compilation, $pivot->claimer),
            ]);

        if ($remote->fileExists("$remoteDir/$fileName")) {
            $remote->delete("$remoteDir/$fileName");
        }

        if ($entries->isEmpty()) {
            $this->complete();

            return;
        }

        $zipPath = $disk->path($fileName);
        $zip = new ZipArchive();

        try {
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException("Unable to open zip at $zipPath");
            }

            foreach ($entries as $id => $name) {
                if (! $zip->addFile($disk->path("clips/$id.mp4"), $name)) {
                    throw new RuntimeException("Unable to add clip $id to zip");
                }

                // videos are already compressed, zip wont be able to compress them any further lol
                $zip->setCompressionName($name, ZipArchive::CM_STORE);
            }

            if (! $zip->close()) {
                throw new RuntimeException("Unable to write zip at $zipPath");
            }

            $fileSize = $disk->fileSize($fileName);

            Log::debug("Uploading $fileName to S3...", [
                'compilation_id' => $remoteDir,
                'size' => $fileSize,
                'size_formatted' => Number::fileSize($fileSize),
            ]);

            $remotePath = $remote->putFileAs($remoteDir, new File($zipPath), $fileName)
                ?: throw new RuntimeException("Unable to upload zip for compilation $remoteDir");
        } finally {
            $disk->delete($fileName);
        }

        $this->complete($remotePath, $fileSize);
    }

    public function failed(?Throwable $e): void
    {
        report($e);

        $this->scopedQuery()->update([
            'processing_status' => CompilationProcessingStatus::Failed,
            'processing_batch_id' => null,
        ]);
    }

    /**
     * @return Builder<Compilation>
     */
    private function scopedQuery(): Builder
    {
        return Compilation::query()
            ->whereKey($this->compilation->getKey())
            ->when($this->batchId !== null, fn (Builder $builder): Builder => $builder
                ->where('processing_batch_id', $this->batchId)
            );
    }

    private function complete(?string $zipPath = null, ?int $fileSize = null): void
    {
        $this->scopedQuery()->update([
            'zip_path' => $zipPath,
            'zip_size' => $fileSize,
            'zip_created_at' => ($zipPath && $fileSize) ? now() : null,
            'processing_status' => $zipPath ? CompilationProcessingStatus::Completed : null,
            'processing_batch_id' => null,
        ]);
    }
}
