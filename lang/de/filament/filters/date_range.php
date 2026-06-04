<?php

declare(strict_types=1);

return [
    'label' => ':name Zwischen',

    'form' => [
        'from' => 'Von',
        'to' => 'Bis',
    ],
    'actions' => [
        'clear_from' => 'Lösche Von',
        'clear_to' => 'Lösche Bis',
    ],
    'indicators' => [
        'from' => ':name Von: :value',
        'to' => ':name Bis: :value',
    ],

    'presets' => [
        'label' => 'Vorlagen',
        'default_options' => [
            'today' => 'Heute',
            'last_7_days' => 'Letzten 7 Tage',
            'last_30_days' => 'Letzten 30 Tage',
            'last_90_days' => 'Letzten 90 Tage',
            'this_month' => 'Dieser Monat',
            'last_month' => 'Letzter Monat',
        ],
    ],
];
