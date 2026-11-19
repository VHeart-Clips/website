<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[WithoutTimestamps]
class UserClipIgnore extends Pivot
{
    use HasFactory;

    /**
     * @return string[]
     */
    public static function getPivotColumns(): array
    {
        return [
            'created_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
