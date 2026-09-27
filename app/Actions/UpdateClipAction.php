<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Clip;
use App\Services\Twitch\Data\ClipDto;
use Illuminate\Support\Facades\Log;

class UpdateClipAction
{
    public function execute(
        Clip $clip,
        ClipDto $dto,
        ?array $only = null,
        bool $ignoreNullValues = true,
        bool $updateNextRefreshAfter = true
    ): void {
        $updates = $only === null
            ? $dto->toModel()
            : array_intersect_key($dto->toModel(), array_flip($only));

        if ($ignoreNullValues) {
            $updates = array_filter($updates, static fn (mixed $value): bool => $value !== null);
        }

        $updates = array_filter(
            $updates,
            static fn (mixed $value, string $key): bool => ! array_key_exists($key, $clip->getAttributes()) || $clip->getOriginal($key) !== $value,
            ARRAY_FILTER_USE_BOTH
        );

        Log::debug('Updating Clip', [
            'clip_id' => $clip->id,
            'clip_slug' => $clip->twitch_id,
            'updated' => array_keys($updates),
        ]);

        if ($updateNextRefreshAfter) {
            $updates['next_refresh_after'] = $clip->getNextRefreshAfter();
        }

        $clip->update($updates);
    }
}
