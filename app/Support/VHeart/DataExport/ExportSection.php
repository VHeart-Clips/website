<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface ExportSection
{
    public function title(): string;

    public function slug(): string;

    public function view(): string;

    public function shouldExport(User $user): bool;

    public function export(User $user): array|Collection|LazyCollection;
}
