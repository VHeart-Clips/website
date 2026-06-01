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
        'resolve-request-accept' => [
            'schema' => [
                'published_compilations' => [
                    'label' => 'Published Compilations',
                    'options' => [
                        'nothing' => [
                            'label' => 'Do Nothing',
                        ],
                        'flag' => [
                            'label' => 'Flag',
                            'description' => 'Clip gets flagged as removed but stays in the Compilation for context',
                        ],
                        'remove' => [
                            'label' => 'Remove',
                            'description' => 'Clip gets removed from the Compilation',
                        ],
                    ],
                ],
                'unpublished_compilations' => [
                    'label' => 'Unpublished Compilations',
                    'options' => [
                        'nothing' => [
                            'label' => 'Do Nothing',
                        ],
                        'flag' => [
                            'label' => 'Flag',
                            'description' => 'Clip gets flagged as removed but stays in the Compilation for context',
                        ],
                        'remove' => [
                            'label' => 'Remove',
                            'description' => 'Clip gets removed from the Compilation',
                        ],
                    ],
                ],
                'internal_compilations' => [
                    'label' => 'Internal Compilations',
                    'options' => [
                        'nothing' => [
                            'label' => 'Do Nothing',
                        ],
                        'flag' => [
                            'label' => 'Flag',
                            'description' => 'Clip gets flagged as removed but stays in the Compilation for context',
                        ],
                        'remove' => [
                            'label' => 'Remove',
                            'description' => 'Clip gets removed from the Compilation',
                        ],
                    ],
                ],
                'clip' => [
                    'label' => 'Clip Action',
                    'options' => [
                        'nothing' => [
                            'label' => 'Do Nothing',
                        ],
                        'block' => [
                            'label' => 'Block',
                            'description' => 'Hides the Clip on the platform and prevents usage in future Compilations.',
                        ],
                        'remove' => [
                            'label' => 'Remove',
                            'description' => 'Removes the Clip from the Platform',
                        ],
                    ],
                ],
            ],
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
