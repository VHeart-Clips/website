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
    'actions' => [
        'resource-link-action' => [
            'group-label' => 'Open',
            'items' => [
                'clip' => 'Clip',
                'broadcaster' => 'Broadcaster',
                'user' => 'User',
                'claimer' => 'Claimer',
                'resolver' => 'Resolver',
            ],
        ],
        'claim' => [
            'label' => 'Claim Request',
        ],
        'unclaim' => [
            'label' => 'Unclaim Request',
        ],
        'force-claim' => [
            'label' => 'Takeover Request',
        ],
        'reset-request' => [
            'label' => 'Reset Request',
        ],
        'resolve-request-group' => [
            'label' => 'Resolve Request',
        ],
    ],
    'notifications' => [
        'claimed' => [
            'title' => 'You have claimed this request',
        ],
        'force-claimed' => [
            'title' => 'This Removal request has been forcefully Claimed',
            'body' => 'This Removal request was claimed by :name :ago',
        ],
        'already-claimed' => [
            'title' => 'Failed to Claim this Removal Request',
            'body' => 'This Removal request was already claimed by :name :ago',
        ],
        'unclaimed' => [
            'title' => 'Failed to Claim this Removal Request',
            'body' => 'This Removal request was already claimed by :name :ago',
        ],
        'resolved' => [
            'title' => 'This Removal request has been :status',
        ],
        'reset-request' => [
            'title' => 'This Removal request has been reset',
        ],
    ],
];
