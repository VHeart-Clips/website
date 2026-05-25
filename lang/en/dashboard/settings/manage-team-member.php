<?php

declare(strict_types=1);

return [
    'section' => [
        'description' => '',
        'model' => [
            'singular' => 'Team Member',
            'plural' => 'Team Members',
        ],
    ],
    'sections' => [
        'twitch_mod_permissions' => [
            'label' => 'Twitch Mod Permissions',
            'description' => '',
        ],
    ],
    'table' => [
        'user' => 'User',
        'permissions' => 'Permission',
    ],
    'filters' => [
        'state' => [
            'label' => 'Allowed',
            'placeholder' => 'All',
            'true' => 'Only Allowed',
            'false' => 'Only Blocked',
        ],
    ],
];
