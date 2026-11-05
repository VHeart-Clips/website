<?php

namespace App\Observers;

use App\Jobs\ClipStatsUpdateJob;
use App\Models\Vote;

class VoteObserver
{
    public function created(Vote $vote): void
    {
        dispatch(new ClipStatsUpdateJob($vote->clip_id));
    }

    public function updated(Vote $vote): void
    {
        dispatch(new ClipStatsUpdateJob($vote->clip_id));
    }

    public function deleted(Vote $vote): void
    {
        dispatch(new ClipStatsUpdateJob($vote->clip_id));
    }
}
