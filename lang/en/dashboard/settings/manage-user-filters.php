<?php

declare(strict_types=1);

return [
    'section' => [
        'description' => '',
        'model' => [
            'singular' => 'User Filter',
            'plural' => 'User Filters',
        ],
    ],
    'table' => [
        'name' => 'User',
        'state' => 'Allowed',
    ],
    'filters' => [
        'state' => [
            'label' => 'Allowed',
            'placeholder' => 'All',
            'true' => 'Only Allowed',
            'false' => 'Only Blocked',
        ],
    ],
    'forms' => [
        'create' => [
            'user-select' => [
                'rules' => [
                    'unique' => 'This user is already in your filter list.',
                    'not_in' => 'You can not add yourself to your own filter list.',
                ],
            ],
        ],
    ],
];
