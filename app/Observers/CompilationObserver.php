<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Discord\Compilation\Status\DeleteCompilationStatusWebhookJob;
use App\Models\Clip\Compilation;

class CompilationObserver
{
    public function created(Compilation $compilation): void
    {
        $compilation->dispatchUpdateWebhook();
    }

    public function updated(Compilation $compilation): void
    {
        $compilation->dispatchUpdateWebhook();
    }

    public function deleted(Compilation $compilation): void
    {
        if ($compilation->discord_message_id) {
            dispatch(new DeleteCompilationStatusWebhookJob($compilation->discord_message_id));
        }
    }

    public function restored(Compilation $compilation): void
    {
        $compilation->dispatchUpdateWebhook();
    }
}
