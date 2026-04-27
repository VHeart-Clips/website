<?php

declare(strict_types=1);

return [
    'submit' => [
        'page_title' => 'Clip einreichen',
        'preview' => [
            'heading' => 'Vorschau',
        ],
        'form' => [
            'heading' => 'Submit clip',
            'fields' => [
                'clip_url' => [
                    'label' => 'Clip-URL*',
                    'placeholder' => 'https://www.twitch.tv/<name>/clip/<id>',
                ],
                'tags' => [
                    'label' => 'Tags',
                    'placeholder' => 'Tags auswählen...',
                    'description' => 'Wähle bis zu 3 Tags aus (minimum 1).',
                    'filter_placeholder' => 'Tags filtern…',
                    'selected_count' => ':count von :max ausgewählt',
                    'no_results' => 'Keine Tags gefunden.',
                    'max_error' => 'Maximal :max Tags auswählbar.',
                    'remove_label' => 'Tag :tag entfernen',
                ],
            ],
            'submit' => 'Clip einreichen',
        ],
        'aside' => [
            'rules' => [
                'heading' => 'Richtlinien',
                'items' => [
                    'max_age' => 'Clips dürfen nicht älter als :age sein.',
                    'minimum_duration' => 'Clips müssen mindestens :duration sekunden lang sein.',
                    'registered' => 'Clip muss von einem registrierten Broadcaster stammen',
                    'consent' => 'Broadcaster muss der Verwendung zugestimmt haben',
                    'no_explicit' => 'Keine expliziten oder beleidigenden Inhalte',
                    'tags_match' => 'Tags müssen zum Clip-Inhalt passen',
                ],
            ],
            'tips' => [
                'heading' => 'Tipps für gute Clips',
                'items' => [
                    'short' => 'Kurze, prägnante Momente',
                    'quality' => 'Gute Audio- und Videoqualität',
                    'funny' => 'Interessante oder lustige Situationen',
                ],
            ],
        ],
    ],

    'vote' => [
        'page_title' => 'Clips Bewerten',
        'form' => [
            'fields' => [
                'vote' => [
                    'label' => 'Stimme für diesen Clip',
                ],
                'skip' => [
                    'label' => 'Diesen Clip überspringen',
                ],
            ],
        ],
        'aside' => [
            'total_votes' => 'Bisherige stimmen für diesen Clip',
            'nothing_left' => 'Wir finden gerade nix was du Voten könntest. Komme bitte später nochmal vorbei!',
        ],
    ],

    'enums' => [
        'clip-feedback-option' => [
            'audio-too-quiet' => 'Ton zu leise',
            'audio-too-loud' => 'Ton zu laut',
            'bad-audio-quality' => 'Schlechte Audio-Qualität',
            'bad-video-quality' => 'Schlechte Video-Qualität',
            'content-unavailable' => 'Inhalt nicht mehr verfügbar',
            'other' => 'Anderes',
        ],
    ],

    'preview' => [
        'consent-required' => 'Erfordert Zustimmung zu externen Diensten um diese Vorschau anzuzeigen.',
    ],

    // Untouched

    'recent' => [
        'title' => 'Deine letzten Einreichungen',
        'no_title' => 'Ohne Titel',
        'open_clip' => 'Zum Clip',
        'views' => 'Aufrufe',
        'empty_title' => 'Noch keine Einreichungen',
        'empty_subtitle' => 'Deine eingereichten Clips erscheinen hier',
    ],

    'login' => [
        'title' => 'Anmeldung erforderlich',
        'subtitle' => 'Du musst angemeldet sein, um Clips einzureichen',
        'alert' => 'Nur angemeldete Benutzer können Clips einreichen, um Spam zu vermeiden.',
        'cta' => 'Jetzt anmelden',
    ],

    'errors' => [
        'login_required' => 'Bitte melde dich an',
        'clip_not_found' => 'Clip nicht gefunden',
        'clip_already_known' => 'Dieser Clip wurde bereits eingereicht',
        'broadcaster_not_allowed' => 'Der Broadcaster hat Clip-Einreichungen nicht erlaubt',
        'category_blocked' => 'Clips von dieser Kategorie bzw. diesem Spiel sind nicht erlaubt',
        'user_not_allowed_for_broadcaster' => 'Du bist für Einreichungen bei diesem Broadcaster nicht zugelassen',
        'clip_url_required' => 'Bitte gib eine Clip-URL ein',
        'cannot_submit' => 'Clip kann nicht eingereicht werden.',
        'daily_limit' => 'Du hast dein tägliches Limit von :limit Einreichungen erreicht.',
        'generic' => 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.',
        'too_old' => 'Dieser Clip ist zu alt. Wir akzeptieren nur Clips, die nicht älter als :age sind.',
        'too_short' => 'Dieser Clip ist zu kurz. Clips müssen mindestens :seconds Sekunden lang sein.',
        'total_limit_reached' => 'Tageslimit erreicht, bitte versuch es morgen wieder.',
        'broadcaster_limit_reached' => 'Tageslimit für diesen Broadcaster erreicht, bitte versuch es morgen wieder.',
    ],

    'status' => [
        'approved' => 'Genehmigt',
        'rejected' => 'Abgelehnt',
        'pending' => 'Ausstehend',
    ],

    'flash' => [
        'submitted' => 'Dein Clip wurde erfolgreich eingereicht!',
    ],
];
