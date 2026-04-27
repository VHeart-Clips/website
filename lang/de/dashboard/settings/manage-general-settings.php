<?php

declare(strict_types=1);

return [
    'sections' => [
        'consent' => [
            'label' => 'Zustimmung Verwalten',
            'description' => 'Änderungen werden möglicherweise erst in der nächsten Folge wirksam, wenn zwischen Donnerstag 00:00 Uhr und Freitag 16:00 Uhr widerrufen wurde.',
        ],
        'default_clip_status' => [
            'label' => 'Standard Clip Status',
            'description' => 'Unabhängig davon, wer Clips einreichen darf, wie sollen wir mit deinen Clips umgehen?',
        ],
        'submissions_settings' => [
            'label' => 'Manage Submissions',
            'description' => 'Wer darf Clips von dir bei uns einreichen? Wir empfehlen, dies für alle Zuschauer freizugeben',
            'form' => [
                'submit_user_allowed' => [
                    'label' => 'Jeder',
                    'description' => 'Jeder kann deine Clips auf unserer Seite einsenden, dies beinhaltet VIPs sowie Mods.',
                ],
                'submit_vip_allowed' => [
                    'label' => 'VIPs',
                    'description' => 'Zuschauer mit dem VIP Status können Clips einsenden',
                ],
                'submit_mods_allowed' => [
                    'label' => 'Moderatoren',
                    'description' => 'Moderatoren können Clips einsenden',
                ],
            ],
        ],
    ],
];
