<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clips\Pages;

use App\Filament\Resources\Clips\ClipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClip extends CreateRecord
{
    protected static string $resource = ClipResource::class;
}
