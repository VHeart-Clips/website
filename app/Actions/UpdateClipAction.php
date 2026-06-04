<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Clip;
use App\Services\Twitch\Data\ClipDto;

class UpdateClipAction
{
    public function execute(
        Clip $clip,
        ClipDto $dto,
        ?array $only = null,
        bool $ignoreNullValues = true,
        bool $updateNextSync = true
    ): void {
        $updates = $only === null
            ? $dto->toModel()
            : array_intersect_key($dto->toModel(), array_flip($only));

        if ($ignoreNullValues) {
            $updates = array_filter($updates, static fn (mixed $value): bool => $value !== null);
        }

        if ($updateNextSync) {
            $updates['next_sync_at'] = $clip->getNextSyncAt();
        }

        $clip->update($updates);
    }
}
