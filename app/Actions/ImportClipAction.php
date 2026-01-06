<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\ClipSubmitted;
use App\Models\Clip;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;

/**
 * Inserts or Updates the given Clip
 */
class ImportClipAction
{
    public function execute(ClipDto $clip, ?User $user = null, ?bool $isAnonymous = false, ?array $tags = null): Clip
    {
        $clipModel = Clip::updateOrCreate([
            'twitch_id' => $clip->id,
        ], $clip->toModel([
            'submitter_id' => $user?->id,
            'is_anonymous' => $isAnonymous,
        ]));

        if (is_array($tags)) {
            $clipModel->tags()->sync($tags);
        }

        ClipSubmitted::dispatch($clipModel, $user, $isAnonymous, $tags);

        return $clipModel;
    }
}
