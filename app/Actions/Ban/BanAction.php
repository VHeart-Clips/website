<?php

declare(strict_types=1);

namespace App\Actions\Ban;

use App\Models\Ban;
use App\Models\Traits\Bannable;
use Carbon\CarbonInterface;
use Carbon\Month;
use Carbon\WeekDay;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BanAction
{
    /**
     * @param  Model&Bannable  $bannable
     */
    public function execute(
        Model $bannable,
        int $bannedByUserId,
        string $reason,
        CarbonInterface|Month|WeekDay|DateTimeInterface|float|int|null|string $until = null
    ): Ban {
        return $bannable
            ->bans()
            ->create([
                'admin_id' => $bannedByUserId,
                'reason' => $reason,
                'banned_until' => (is_null($until) || $until instanceof CarbonInterface) ? $until : Carbon::parse($until),
            ]);
    }
}
