<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Discord\Compilation\Status\DeleteCompilationStatusWebhookJob;
use App\Models\Clip;
use App\Models\Clip\CompilationClip;

class CompilationClipObserver
{
    public function created(CompilationClip $clip): void
    {
        $clip->compilation->dispatchUpdateWebhook();
    }

    public function updated(CompilationClip $clip): void
    {
        if ($clip->wasChanged('compilation_id')) {
            Clip\Compilation::find($clip->getOriginal('compilation_id'))?->dispatchUpdateWebhook();
        }

        $clip->load('compilation')->compilation->dispatchUpdateWebhook();
    }

    public function deleted(CompilationClip $clip): void
    {
        if ($clip->compilation->discord_message_id) {
            dispatch(new DeleteCompilationStatusWebhookJob($clip->compilation->discord_message_id));
        }
    }

    public function restored(CompilationClip $clip): void
    {
        $clip->compilation->dispatchUpdateWebhook();
    }
}
