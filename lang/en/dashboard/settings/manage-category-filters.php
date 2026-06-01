<?php

declare(strict_types=1);

return [
    'section' => [
        'description' => '',
        'model' => [
            'singular' => 'Category Filter',
            'plural' => 'Category Filters',
        ],
    ],
    'table' => [
        'title' => 'Category',
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
            'category-select' => [
                'rules' => [
                    'unique' => 'Category is already in the filter list.',
                ],
            ],
        ],
    ],
];
