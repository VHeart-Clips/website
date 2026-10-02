<?php

declare(strict_types=1);

return [
    'enums' => [
        'report-status' => [
            'pending' => 'Wartend',
            'in-review' => 'In Bearbeitung',
            'resolved' => 'Fertiggestellt',
            'dismissed' => 'Verworfen',
        ],
        'report-reason' => [
            'other' => 'Anderes',
            'spam' => 'Spam',
            'harassment' => 'Belästigung',
            'hate-speech' => 'Hassrede',
            'ai-content' => 'AI Inhalte',
            'content-unavailable' => 'Inhalt nicht verfügbar',
        ],
        'report-category-details-type' => [
            'disabled' => 'Deaktiviert',
            'optional' => 'Optional',
            'required' => 'Benötigt',
        ],
        'resolve-action' => [
            'other' => 'Anderes',
            'dismissed' => 'Report Verworfen',
            'content-edited' => 'Inhalte Angepasst',
            'content-removed' => 'Inhalte entfernt',
            'user-banned' => 'Benutzer Gesperrt',
        ],
    ],
    'modal' => [
        'title' => ':reportable Melden',
        'subtitle' => 'Bitte beschreibe dein Problem',
        'inputs' => [
            'reason' => [
                'label' => 'Grund für die Meldung',
            ],
            'description' => [
                'label' => 'Weitere Informationen',
                'placeholder' => 'Benötigt wenn "Anderes" gewählt.',
            ],
            'cancel' => 'Abbrechen',
            'submit' => 'Melden',
        ],
        'success' => [
            'title' => 'Erfolgreich gemeldet!',
            'message' => 'Wir werden uns darum kümmern.',
            'report-id' => 'Bei fragen bitte folgende nummer angeben: ',
            'ok' => 'Verstanden',
        ],
        'button' => ':reportable Melden',
    ],
    'reportable' => [
        'user' => 'Benutzer',
        'clip' => 'Clip',
    ],
];
