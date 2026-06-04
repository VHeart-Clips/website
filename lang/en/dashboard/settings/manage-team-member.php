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
    'forms' => [
        'create' => [
            'user-select' => [
                'rules' => [
                    'unique' => 'This user is already in your team.',
                    'not_in' => 'You can not add yourself to your own team.',
                ],
            ],
        ],
    ],
];
