<?php

declare(strict_types=1);

return [
    'clips' => [
        'submit_action' => [
            'label' => 'Submit Clip',
            'form' => [
                'uri' => 'Clip URI',
                'tags' => 'Tags',
                'bypass' => [
                    'label' => 'Bypass Limitations',
                    'description' => 'You have Permission to bypass specific limitations, do not abuse.',
                    'options' => [
                        'broadcaster_consent' => 'Broadcaster Consent',
                        'category_ban' => 'Site Category Ban',
                        'minimum_length' => 'Minimum Length',
                        'maximum_age' => 'Maximum Age',
                    ],
                ],
            ],
        ],
        'update_clip_status' => [
            'label' => 'Update Clip Status',
            'form' => [
                'status' => 'Status',
            ],
        ],
    ],
];
