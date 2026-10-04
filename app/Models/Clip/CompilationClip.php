<?php

declare(strict_types=1);

namespace App\Models\Clip;

use App\Enums\Clips\CompilationClipClaimStatus;
use App\Models\Clip;
use App\Models\User;
use Database\Factories\Clip\CompilationClipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

// Pivot models are only ever used for Model to Model relationship extensions
// e.g. for casts on a pivot or for adding relationships that are stored on the pivot
// basically stuff that does not belong to any of the "actual" models as they may require
// pivot data, this is usually accessible via $model->pivot->stuff()
class CompilationClip extends Pivot
{
    /** @use HasFactory<CompilationClipFactory> */
    use HasFactory;

    /**
     * Get the list of columns for this pivot
     *
     * @return string[]
     */
    public static function getPivotColumns(): array
    {
        return [
            'added_by',
            'added_at',
            'claimed_by',
            'claim_status',
            'claimed_at',
            'removed_at',
            'file_path',
            'file_size',
            'file_duration',
        ];
    }

    public static function recommendedFileName(Clip $clip, ?Compilation $compilation, ?User $cutter): string
    {
        $clip->loadMissing(['owner', 'creator', 'category']);

        $broadcaster = $clip->owner->name;
        $cutterName = $cutter?->name ?? 'Unknown Cutter';
        $clipper = $clip->creator?->name ?? 'Unknown Clipper';
        $category = $clip->category->title;
        $episode = $compilation?->title ?? 'Unknown Episode';

        return self::sanitizeFilename("{$clip->id}__{$broadcaster}__{$category}__{$cutterName}__{$clipper}__{$episode}.mp4");
    }

    /**
     * @return BelongsTo<Clip, $this>
     */
    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }

    /**
     * @return BelongsTo<Compilation, $this>
     */
    public function compilation(): BelongsTo
    {
        return $this->belongsTo(Compilation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    protected function casts(): array
    {
        return [
            'claim_status' => CompilationClipClaimStatus::class,
            'added_at' => 'datetime',
            'claimed_at' => 'datetime',
            'removed_at' => 'datetime',
            'file_size' => 'integer',
            'file_duration' => 'integer',
            'file_metadata' => 'array',
        ];
    }

    // https://stackoverflow.com/a/42058764 by mgutt. License - CC BY-SA 4.0
    private static function sanitizeFilename(string $filename): string
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
