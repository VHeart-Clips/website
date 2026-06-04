<?php

declare(strict_types=1);

return [
    'section' => [
        'description' => '',
        'model' => [
            'singular' => 'Team Mitglied',
            'plural' => 'Team Mitglieder',
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
        'permissions' => 'Rechte',
    ],
    'forms' => [
        'create' => [
            'user-select' => [
                'rules' => [
                    'unique' => 'Dieser Benutzer ist bereits in deinem Team.',
                    'not_in' => 'Du kannst dich nicht selber zu deinem Team hinzufügen.',
                ],
            ],
        ],
    ],
];
