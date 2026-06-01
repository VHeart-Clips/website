<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use Database\Factories\ShortUrlFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    use Auditable;

    /** @use HasFactory<ShortUrlFactory> */
    use HasFactory;
}
