<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Clip;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Throwable;

class ArchiveClipVotesAction
{
    /**
     * @throws RuntimeException|LogicException|Throwable
     */
    public function execute(Clip $clip): void
    {
        throw_unless(
            isset($clip->jury_votes, $clip->public_votes, $clip->score),
            RuntimeException::class,
            'Clip must be loaded with vote and score count before archiving.',
        );

        throw_if(
            $clip->final_score !== null,
            LogicException::class,
            'Already archived clips can not be archived again.',
        );

        DB::transaction(static function () use ($clip): void {
            $clip->update([
                'final_jury_votes' => $clip->jury_votes,
                'final_public_votes' => $clip->public_votes,
                'final_score' => $clip->score,
            ]);

            $clip->votes()->delete();
        });
    }
}
