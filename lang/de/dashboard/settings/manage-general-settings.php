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
    'actions' => [
        'apply_default_status' => [
            'label' => 'Anwenden',
            'modal' => [
                'heading' => 'Standard-Status auf Clips anwenden',
                'description' => 'Überschreibt Clip-Status mit deinem aktuellen Standard (:status).',
                'submit' => 'Anwenden',
                'helper_text' => 'Alle ausgewählten Clips werden auf deinen Standard-Status gesetzt. Das kann nicht automatisch rückgängig gemacht werden. Clips welche bereits einer Compilation angehören werden immer ausgenommen sein.',
            ],
            'notifications' => [
                'none_matched' => 'Keine Clips mit den gewählten Status gefunden',
                'success' => [
                    'title' => ':count Clip aktualisiert|:count Clips aktualisiert',
                    'body' => ':count Clip wurde auf :status gesetzt|:count Clips wurden auf :status gesetzt',
                ],
                'rate_limited' => [
                    'title' => 'Bitte warten',
                    'body' => 'Versuche es in :seconds sekunden erneut.',
                ],
            ],
        ],
    ],
];
