<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomFileUpload extends Field
{
    public const string TMP_PREFIX = 'tmp/';

    protected string $view = 'filament.forms.components.custom-file-upload';

    protected string $directory = '';

    protected string $disk = 's3';

    protected ?int $maxSize = null;

    /**
     * @var array<string, string> mime type => extension
     */
    protected array $acceptedFileTypes = ['video/mp4' => 'mp4'];

    protected ?string $replacedPath = null;

    /**
     * @var array<string, string> tmp key => final key
     */
    protected array $moved = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function (mixed $state): ?string {
            $original = $this->originalState();
            $state = is_string($state) && filled($state) ? $state : null;

            $result = match (true) {
                $state !== null && str_starts_with($state, self::TMP_PREFIX) => $this->moved[$state] ??= $this->moveToFinal($state),
                $state === $original => $state,
                default => null,
            };

            $this->replacedPath = $original !== $result ? $original : null;

            return $result;
        });

        $this->saveRelationshipsUsing(function (): void {
            if ($this->replacedPath === null) {
                return;
            }

            Storage::disk($this->disk)->delete($this->replacedPath);
            $this->replacedPath = null;
        });

        $this->rules([
            fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                if (blank($value) || $value === $this->originalState()) {
                    return;
                }

                if (! str_starts_with($value, 'tmp/')) {
                    return;
                }

                if (! self::isTemporaryPrefixOf($value, auth()->id())) {
                    $fail('Invalid file.');

                    return;
                }

                $disk = Storage::disk($this->disk);

                if (! $disk->exists($value)) {
                    Log::debug('Uploaded file is missing', [
                        'path' => $value,
                        'disk' => $this->disk,
                        'user_id' => auth()->id(),
                    ]);

                    $fail('The uploaded file could not be found.');

                    return;
                }

                if ($this->maxSize && ($size = $disk->size($value)) > $this->maxSize * 1024) {
                    Log::debug('Uploaded file is larger than expected', [
                        'path' => $value,
                        'size' => $size,
                        'size_limit' => $this->maxSize * 1024,
                        'disk' => $this->disk,
                        'user_id' => auth()->id(),
                    ]);

                    $disk->delete($value);
                    $fail('The uploaded file is too large.');
                }

                Log::debug('File passes requirements', [
                    'path' => $value,
                    'disk' => $this->disk,
                    'user_id' => auth()->id(),
                ]);
            },
        ]);
    }

    /**
     * Generates a tamperproof path prefix for temporary uploads
     *
     * Example: `tmp/1119ec0405fe1284eb35805b99bb167d92c08ce9e6a6ed26ac42b8627c7e59d1/`
     */
    public static function temporaryPrefixFor(int|string $userId): string
    {
        return self::TMP_PREFIX.hash_hmac('sha256', (string) $userId, (string) config('app.key')).'/';
    }

    /**
     * Checks if the provided path is a valid temporary path for uploads owned by the user id
     */
    public static function isTemporaryPrefixOf(mixed $key, int|string $userId): bool
    {
        if (! is_string($key)) {
            return false;
        }

        $prefix = self::temporaryPrefixFor($userId);

        if (! Str::startsWith($key, $prefix)) {
            return false;
        }

        return Str::isMatch('/^[0-9a-f-]{36}\.[a-z0-9]{1,8}$/i', Str::after($key, $prefix));
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function maxSize(int $maxSize): static
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * @param  array<string, string>  $types  mime type => extension
     */
    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getAcceptedFileTypes(): array
    {
        return $this->acceptedFileTypes;
    }

    /**
     * Generates a token the user can use for our presign route to generate temporary upload urls
     * lifetime is limited and its also scoped to the current user to prevent abuse.
     */
    public function getUploadConfigToken(int $ttl = 60): string
    {
        return Crypt::encrypt([
            'user_id' => auth()->id(),
            'expires_at' => now()->addMinutes($ttl)->timestamp,
            'maxSize' => $this->maxSize,
            'acceptedFileTypes' => $this->acceptedFileTypes,
            'disk' => $this->disk,
        ]);
    }

    protected function originalState(): ?string
    {
        $original = $this->getRecord()?->getRawOriginal($this->getName());

        return is_string($original) && filled($original) ? $original : null;
    }

    protected function moveToFinal(string $tmpKey): string
    {
        abort_unless(self::isTemporaryPrefixOf($tmpKey, auth()->id()), 422);

        $final = mb_trim(mb_trim($this->directory, '/').'/'.basename($tmpKey), '/');

        Log::debug('Moving uploaded file', [
            'old' => $tmpKey,
            'new' => $final,
            'disk' => $this->disk,
            'user_id' => auth()->id(),
        ]);

        abort_unless(Storage::disk($this->disk)->move($tmpKey, $final), 500);

        return $final;
    }
}
