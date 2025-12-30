<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'page_title' => 'Clip einreichen',
    'breadcrumb' => 'Clip einreichen',
    'headline' => 'Clip einreichen',

    'preview' => [
        'title' => 'Vorschau',
        'placeholder' => 'Vorschau wird hier angezeigt',
        'loading' => 'Wird geprüft…',
    ],

    'submit' => [
        'title' => 'Einreichen',
        'clip_url_label' => 'Clip-URL *',
        'clip_url_placeholder' => 'https://www.twitch.tv/<name>/clip/<id> oder https://clips.twitch.tv/<id>',
        'tags_label' => 'Tags',
        'anonymous' => 'Anonym einreichen',
        'anonymous_hint' => '(Dein Name wird nicht öffentlich angezeigt)',
        'cta' => 'Clip einreichen',
    ],

    'rules' => [
        'title' => 'Richtlinien',
        'items' => [
            'registered' => 'Clip muss von einem registrierten Broadcaster stammen',
            'consent' => 'Broadcaster muss der Verwendung zugestimmt haben',
            'no_explicit' => 'Keine expliziten oder beleidigenden Inhalte',
            'tags_match' => 'Tags müssen zum Clip-Inhalt passen',
        ],
    ],

    'tips' => [
        'title' => 'Tipps für gute Clips',
        'items' => [
            'short' => 'Kurze, prägnante Momente',
            'quality' => 'Gute Audio- und Videoqualität',
            'funny' => 'Interessante oder lustige Situationen',
        ],
    ],

    'recent' => [
        'title' => 'Deine letzten Einreichungen',
        'no_title' => 'Ohne Titel',
        'open_clip' => 'Zum Clip',
        'views' => 'Aufrufe',
        'empty_title' => 'Noch keine Einreichungen',
        'empty_subtitle' => 'Deine eingereichten Clips erscheinen hier',
    ],

    'status' => [
        'approved' => 'Genehmigt',
        'rejected' => 'Abgelehnt',
        'pending' => 'Ausstehend',
    ],

    'errors' => [
        'login_required' => 'Bitte melde dich an',
        'clip_url_required' => 'Bitte gib eine Clip-URL ein',
        'cannot_submit' => 'Clip kann nicht eingereicht werden.',
        'daily_limit' => 'Du hast dein tägliches Limit von :limit Einreichungen erreicht.',
        'generic' => 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.',
    ],

    'login' => [
        'title' => 'Anmeldung erforderlich',
        'subtitle' => 'Du musst angemeldet sein, um Clips einzureichen',
        'alert' => 'Nur angemeldete Benutzer können Clips einreichen, um Spam zu vermeiden.',
        'cta' => 'Jetzt anmelden',
    ],
];
