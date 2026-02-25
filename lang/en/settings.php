<?php

declare(strict_types=1);

return [
    'title' => 'Settings',
    'description' => 'Manage your profile and account settings',
    'nav' => [
        'profile' => 'Profile',
        'two_factor' => 'Two-Factor Auth',
        'appearance' => 'Appearance',
        'permissions' => 'Permissions',
    ],
    'permissions' => [
        'title' => 'Permissions',
        'description' => 'Manage which permissions you grant to VHeart.',
        'clip_title' => 'Clip permission',
        'clip_description' => 'Allow us to use your configured clips.',
        'clip_disclaimer' => 'Changes may take effect in the next episode if you revoke between Thu 00:00 and Fri 16:00.',
        'granted' => 'Permission granted',
        'revoked' => 'Permission not granted',
        'toggle_label' => 'Allow clip usage',
    ],
];
