<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Clip;
use Illuminate\Support\Facades\Log;

class DeleteClipAction
{
    public function execute(Clip $clip, bool $soft = false): void
    {
        if ($soft || $clip->compilations()->exists()) {
            Log::debug('Clip belongs to compilations, soft deleting only.');

            $clip->delete();

            return;
        }

        Log::debug('Clip has no compilations, wiping completely.');
        $clip->votes()->forceDelete();
        $clip->comments()->forceDelete();
        $clip->tags()->detach();
        $clip->forceDelete();
    }
}
