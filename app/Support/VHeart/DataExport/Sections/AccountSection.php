<?php

declare(strict_types=1);

namespace App\Support\VHeart\DataExport\Sections;

use App\Models\User;
use App\Support\VHeart\DataExport\BaseSection;

class AccountSection extends BaseSection
{
    public function title(): string
    {
        return 'Account';
    }

    public function export(User $user): array
    {
        $user->loadMissing('roles');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601ZuluString(),
            'roles' => $user->roles->pluck('name')->all(),
            'language' => $user->language,
            'two_factor' => [
                'has_email_authentication' => $user->has_email_authentication,
                'has_app_authentication' => filled($user->app_authentication_secret),
            ],
            'created_at' => $user->created_at?->toIso8601ZuluString(),
            'updated_at' => $user->updated_at?->toIso8601ZuluString(),
            'deleted_at' => $user->deleted_at?->toIso8601ZuluString(),
        ];
    }
}
