<?php

declare(strict_types=1);

return [
    'clips' => [
        'submit_action' => [
            'label' => 'Clip Einsenden',
            'form' => [
                'uri' => 'Clip URI',
                'tags' => 'Tags',
                'bypass' => [
                    'label' => 'Limitierungen Ignorieren',
                    'description' => 'Du hast die möglichkeit bestimmte Limitierungen zu ignorieren, bitte nicht ausnutzen.',
                    'options' => [
                        'broadcaster_consent' => 'Broadcaster Zustimmung',
                        'category_ban' => 'Kategorie Seiten-Bann',
                        'minimum_length' => 'Minimale Länge',
                        'maximum_age' => 'Maximales Alter',
                    ],
                ],
            ],
        ],
        'update_clip_status' => [
            'label' => 'Clip Status Ändern',
            'form' => [
                'status' => 'Clip Status',
            ],
        ],
    ],
];
