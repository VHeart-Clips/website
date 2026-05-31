<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Removal Request',
        'label_plural' => 'Removal Requests',
    ],
    'infolist' => [
        'status' => 'Request Status',
        'claimed_by' => [
            'label' => 'Claimed By',
            'placeholder' => 'No one claimed this yet',
        ],
        'claimed_at' => 'Claimed At',
        'resolved_by' => [
            'label' => 'Resolved By',
            'placeholder' => 'No one resolved this yet',
        ],
        'resolved_at' => 'Resolved At',
        'details' => [
            'label' => 'Additional Details',
            'placeholder' => 'No additional details provided',
        ],
        'discussion' => [
            'hint' => '(may be visible to broadcasters in the future)',
        ],
    ],
    'table' => [
        'columns' => [
            'status' => 'Removal Status',
        ],
    ],
    'filters' => [
        'resolved' => [
            'options' => [
                'true' => 'Include resolved',
                'false' => 'Only resolved',
                'placeholder' => 'Hide resolved',
            ],
        ],
    ],
    'actions' => [],
    'notifications' => [],
];
