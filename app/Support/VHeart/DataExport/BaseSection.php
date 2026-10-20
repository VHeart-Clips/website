<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport;

use App\Models\User;
use Illuminate\Support\Str;

abstract class BaseSection implements ExportSection
{
    public function shouldExport(User $user): bool
    {
        return true;
    }

    public function slug(): string
    {
        return Str::slug($this->title(), language: app()->currentLocale());
    }

    public function view(): string
    {
        return $this->slug();
    }
}
