<?php

declare(strict_types=1);

return [
    'form' => [
        'title' => 'Titel',
        'twitch_category' => 'Twitch Kategorie',
        'duration' => 'Länge',
        'broadcaster' => 'Broadcaster',
        'creator' => 'Clip Ersteller',
        'submitted_by' => 'Eingesendet Von',
        'category' => 'Kategorie',
        'created_at' => 'Clip Erstellt am',
        'tags' => 'Tags',
        'status' => 'Status',
    ],
    'infolist' => [
        'title' => 'Titel',
        'category' => 'Kategorie',
        'duration' => 'Länge',
        'broadcaster' => 'Broadcaster',
        'creator' => 'Clip Ersteller',
        'submitted_by' => 'Eingesendet Von',
        'submitted_at' => 'Eingesendet Am',
        'created_at' => 'Clip Erstellt am',
    ],
    'table' => [
        'columns' => [
            'twitch_id' => 'Twitch ID',
            'thumbnail' => 'Vorschau',
            'title' => 'Titel',
            'broadcaster' => 'Broadcaster',
            'creator' => 'Clip Ersteller',
            'submitter' => 'Einsender',
            'category' => 'Kategorie',
            'duration' => 'Länge',
            'status' => 'Status',
            'votes' => 'Stimmen',
            'submitted_at' => 'Eingesendet Am',
            'created_at' => 'Clip Erstellt am',
            'updated_at' => 'Aktualisiert Am',
            'deleted_at' => 'Entfernt Am',
        ],
    ],
    'filters' => [
        'broadcaster' => 'Broadcaster',
        'creator' => 'Clip Ersteller',
        'submitter' => 'Einsender',
        'category' => 'Kategorie',
        'tags' => 'Tags',
        'status' => 'Status',
        'status_visibility' => [
            'label' => 'Status',
            'placeholder' => 'Alle',
            'true' => 'Nur Geblockt',
            'false' => 'Nur Bestätigt',
        ],

        'created_range' => [
            'label' => 'Erstellt Zwischen',
            'indicator' => 'Clip Erstellt Zwischen',
        ],

        'submission_range' => [
            'label' => 'Eingesendet Zwischen',
            'indicator' => 'Clip Eingesendet Zwischen',
        ],
    ],
    'edit' => [
        'title' => ':label von :broadcaster bearbeiten',
    ],
    'actions' => [
        'view_on_twitch' => 'Auf Twitch ansehen',
    ],
    'notifications' => [],
];
