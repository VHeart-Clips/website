<?php

declare(strict_types=1);

namespace App\Models\Clip;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Enums\Compilation\CompilationProcessingStatus;
use App\Jobs\Compilations\CompilationUpdateClipFilesJob;
use App\Jobs\Discord\Compilation\Status\CompilationStatusWebhookJob;
use App\Models\Clip;
use App\Models\Contracts\FilamentResourceful;
use App\Models\Traits\Auditable;
use App\Models\User;
use App\Policies\CompilationPolicy;
use Database\Factories\Clip\CompilationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

#[UsePolicy(CompilationPolicy::class)]
class Compilation extends Model implements Commentable, FilamentResourceful
{
    /** @use HasFactory<CompilationFactory> */
    use Auditable, HasComments, HasFactory, SoftDeletes;

    public string $filamentResourcePageForCommentNotifications = 'edit';

    /**
     * User who has Created the Compilation (if available)
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dispatchUpdateWebhook(bool $force = false): bool
    {
        if (blank(config('services.discord.webhooks.compilation'))) {
            Log::warning('Compilation Webhook has not been configured');

            return false;
        }

        if (
            ! $force
            && ($this->user_id !== 0 || $this->status === CompilationStatus::Internal)
            && blank($this->discord_message_id)
        ) {
            return false;
        }

        if (! $force && $this->wasChanged()) {
            $changes = array_keys($this->getChanges());
            $changesWeCareAbout = array_diff($changes, [
                'slug',
                'type',
                'description',
                'youtube_url',
                'discord_message_id',
                'updated_at',
                'processing_status',
                'processing_batch_id',
                'zip_path',
                'zip_size',
                'zip_created_at',
            ]);

            if ($changesWeCareAbout === []) {
                return false;
            }
        }

        dispatch(new CompilationStatusWebhookJob($this));

        return true;
    }

    /**
     * @return BelongsToMany<Clip, $this, Pivot>
     */
    public function clips(): BelongsToMany
    {
        return $this->belongsToMany(Clip::class, CompilationClip::class)
            ->using(CompilationClip::class)
            ->withPivot(CompilationClip::getPivotColumns())
            ->withTimestamps();
    }

    /**
     * Schedules the export process for the zip containing all (completed) clip files.
     */
    public function scheduleExport(): self
    {
        if (! $this->processing_status?->isProcessing()) {
            $this->update(['processing_status' => CompilationProcessingStatus::Scheduled]);
        }

        dispatch(new CompilationUpdateClipFilesJob($this));

        return $this;
    }

    public function isReadOnly(): bool
    {
        if ($this->status->isScheduled()) {
            return true;
        }

        return $this->status->isPublic();
    }

    public function getDisk(string $disk = 'local'): LocalFilesystemAdapter
    {
        return Storage::build([
            'driver' => 'scoped',
            'disk' => $disk,
            'prefix' => "compilations/$this->id",
        ]);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    #[Scope]
    protected function whereAttachable(Builder $query): Builder
    {
        return $query
            ->whereNotIn('compilations.status', CompilationStatus::getVoteDisabledCases())
            ->whereNot(fn (Builder $builder) => $builder
                ->where('compilations.status', CompilationStatus::Internal)
                ->whereNot('compilations.user_id', auth()->id()));
    }

    protected function casts(): array
    {
        return [
            'status' => CompilationStatus::class,
            'type' => CompilationType::class,
            'processing_status' => CompilationProcessingStatus::class,
            'claimed_at' => 'datetime',
            'zip_created_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }
}
