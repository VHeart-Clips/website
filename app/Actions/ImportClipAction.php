<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Clips\ClipStatus;
use App\Events\ClipSubmitted;
use App\Jobs\ImportCategoryJob;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Clip;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;

/**
 * Inserts or Updates the given Clip
 */
class ImportClipAction
{
    public function execute(ClipDto $clip, ?User $user = null, ?array $tags = null): Clip
    {
        /** @var Clip $clipModel */
        $clipModel = Clip::firstOrCreate([
            'twitch_id' => $clip->id,
        ], $clip->toModel([
            'submitter_id' => $user?->id,
            'status' => Broadcaster::select('default_clip_status')->find($clip->broadcasterId)?->default_clip_status ?? ClipStatus::Unknown,
        ]));

        if (is_array($tags)) {
            $clipModel->tags()->sync($tags);
        }

        ImportCategoryJob::dispatch($clipModel->category_id)
            // some time to breath for other submissions, take as many as possible.
            ->delay(now()->addSeconds(5));

        ClipSubmitted::dispatch($clipModel, $user, $tags);

        return $clipModel;
    }
}
