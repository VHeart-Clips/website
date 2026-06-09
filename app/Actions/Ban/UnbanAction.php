<?php

declare(strict_types=1);

namespace App\Actions\Ban;

use App\Models\Ban;

class UnbanAction
{
    public function execute(Ban $ban, int $unbannedByUserID = 0): bool
    {
        return $ban
            ->update([
                'unbanned_at' => now(),
                'unbanned_by' => $unbannedByUserID,
            ]);
    }
}
