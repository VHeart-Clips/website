<?php

declare(strict_types=1);

return [
    'section' => [
        'description' => '',
        'model' => [
            'singular' => 'Kategorie Filter',
            'plural' => 'Kategorie Filters',
        ],
    ],
    'table' => [
        'title' => 'Kategorie',
        'state' => 'Erlaubt',
    ],
    'filters' => [
        'state' => [
            'label' => 'Erlaubt',
            'placeholder' => 'Alle',
            'true' => 'Nur Erlaubte',
            'false' => 'Nur Geblockte',
        ],
    ],
    'forms' => [
        'create' => [
            'category-select' => [
                'rules' => [
                    'unique' => 'Die Kategorie ist bereits in deiner liste.',
                ],
            ],
        ],
    ],
];
