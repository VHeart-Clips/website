<?php

declare(strict_types=1);

return [
    'title' => 'Broadcaster Einrichtung',
    'heading' => 'Willkommen, :username!',
    'setup' => [
        'heading' => 'Lass uns dein Profil einrichten.',
        'consent' => [
            'heading' => 'Nutzungsrechte für deine Inhalte',
            'subheading' => 'Damit wir deine Clips in unseren Compilations verwenden können, brauchen wir deine Erlaubnis. Du kannst das natürlich überspringen und dir erstmal alles in Ruhe anschauen.',
        ],
        'default_clip_status' => [
            'heading' => 'Standard Clip Status',
            'subheading' => 'Unabhängig davon, wer Clips einreichen darf, wie sollen wir mit deinen Clips umgehen?',
            'options' => [
                'approved' => 'Clips können sofort nach dem Einreichen für Compilations verwendet werden',
                'need_approval' => 'Clips müssen zuerst von dir im Dashboard freigegeben werden, bevor wir sie für Compilations verwenden können',
            ],
        ],
        'submissions' => [
            'heading' => 'Clip-Einsendungen',
            'subheading' => 'Wer darf Clips von dir bei uns einreichen? Wir empfehlen, dies für alle Zuschauer freizugeben.',
            'options' => [
                'everyone' => [
                    'label' => 'Jeder',
                    'description' => 'Jeder kann deine Clips auf unserer Seite einsenden, dies beinhaltet VIPs sowie Mods.',
                ],
                'vips' => [
                    'label' => 'VIPs',
                    'description' => 'Zuschauer mit dem VIP Status können Clips einsenden',
                ],
                'mods' => [
                    'label' => 'Moderatoren',
                    'description' => 'Moderatoren können Clips einsenden',
                ],
            ],
        ],
        'later' => 'Später entscheiden',
        'submit' => 'Speichern & Weiter',
    ],
    'alert' => [
        'heading' => 'Streamer?',
        'description' => 'Bitte erstelle ein Streamer-Profil und erteile uns die Erlaubnis, deine Inhalte zu verwenden.',
        'cta' => 'Profil einrichten',
        'dismiss' => 'Schließen',
    ],
];
