<?php

declare(strict_types=1);

return [
    'settings' => [
        'title' => 'Einstellungen',
        'broadcaster_note' => [
            'label' => 'Hinweis für Broadcaster',
            'description' => 'Um spezifische Einstellungen für deinen Kanal zu verwalten, wechsle bitte in das Broadcaster Dashboard.',
            'link' => 'Zum Broadcaster Dashboard',
        ],
        'data-export' => [
            'heading' => 'Deine Daten',
            'subheading' => 'Kopie deiner Daten herunterladen',
            'description' => 'Lade eine Kopie aller persönlichen Daten herunter, die wir zu deinem Konto gespeichert haben.',
            'confirmation' => 'Gib zur Bestätigung deinen Zwei-Faktor Code ein',
            'submit' => 'Meine Daten herunterladen',
        ],
        'delete' => [
            'heading' => 'Konto Löschen',
            'subheading' => 'Möchtest du dein Konto wirklich löschen?',
            'description' => 'Dein Broadcaster-Profil und persönliche Daten werden gelöscht. Aus technischen Gründen bleiben deine Twitch-Nutzer-ID, von dir abgegebene Stimmen bis zur Archivierung sowie von dir eingereichte Clips anderer Kanäle erhalten. Clips von deinem eigenen Kanal werden jedoch sofort ausgeblendet und erst wieder sichtbar, falls du in Zukunft ein neues Broadcaster-Profil erstellst.',
            'confirmation' => [
                'two-factor' => [
                    'label' => 'Gib zur Bestätigung deinen Zwei-Faktor Code ein',
                ],
                'keyword' => [
                    'label' => 'Gib zur Bestätigung <code>:keyword</code> ein.',
                    'keyword' => 'KONTO LOESCHEN',
                ],
            ],
            'submit' => 'Konto Löschen',
        ],
    ],
    'statistics' => [
        'title' => 'Deine Statistiken',
        'heading' => 'Deine Statistiken',
        'subheading' => '',
        'description' => '',
        'stats' => [
            'clips-submitted' => [
                'title' => 'Eingereichte Clips',
                'details' => 'Eingereichte Clips Anzeigen',
                'stats' => [
                    'total' => 'Total',
                    'week' => 'Diese Woche',
                ],
            ],
            'votes' => [
                'title' => 'Votes',
                'details' => 'Votes Anzeigen',
                'stats' => [
                    'total' => 'Total',
                    'week' => 'Diese Woche',
                    '30days' => 'Letzte 30 Tage',
                ],
            ],
        ],
    ],
    'statistics_details' => [
        'title' => 'Deine Statistiken - :name',
        'infinite-loader' => [
            'no-more' => 'Keine weiteren :name verfügbar',
            'nothing-found' => 'Keine :name gefunden',
            'nothing-found-subtext' => 'Gerade sind keine :name verfügbar.',
        ],
        'votes' => [
            'name' => 'Votes',
            'heading' => 'Deine Votes',
            'subheading' => '',
            'description' => '',
        ],
        'submitted-clips' => [
            'name' => 'Eingereichten Clips',
            'heading' => 'Deine Eingereichten Clips',
            'subheading' => '',
            'description' => '',
        ],
    ],
];
