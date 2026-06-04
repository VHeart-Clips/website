<?php

declare(strict_types=1);

return [
    'section' => [
        'description' => 'Verwalte deine Benutzer spezifischen Regeln um zu steuern wer deine Clips einsenden darf oder nicht.',
        'model' => [
            'singular' => 'Benutzer regel',
            'plural' => 'Benutzer regeln',
        ],
    ],
    'table' => [
        'name' => 'Benutzer',
        'state' => 'Erlaubt',
    ],
    'filters' => [
        'state' => [
            'label' => 'Erlaubt',
            'placeholder' => 'Alle',
            'true' => 'Nur Erlaubt',
            'false' => 'Nicht Erlaubt',
        ],
    ],
    'forms' => [
        'create' => [
            'user-select' => [
                'rules' => [
                    'unique' => 'Dieser Benutzer ist bereits vorhanden.',
                    'not_in' => 'Du kannst dich nicht selber hinzufügen.',
                ],
            ],
        ],
    ],
];
