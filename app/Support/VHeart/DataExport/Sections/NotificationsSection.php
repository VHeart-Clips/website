<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\LazyCollection;

class NotificationsSection extends BaseSection
{
    public function title(): string
    {
        return 'Notifications';
    }

    public function shouldExport(User $user): bool
    {
        return $user->notifications()->exists();
    }

    public function export(User $user): LazyCollection
    {
        return $user->notifications()
            ->lazyById(1000, 'id')
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'read_at' => $notification->read_at?->toIso8601ZuluString(),
                'created_at' => $notification->created_at?->toIso8601ZuluString(),
                'updated_at' => $notification->updated_at?->toIso8601ZuluString(),
            ]);
    }
}
