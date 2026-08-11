<?php

declare(strict_types=1);

return [
    'settings' => [
        'title' => 'Account Settings',
        'broadcaster_note' => [
            'label' => 'Notice for Broadcasters',
            'description' => 'To manage specific settings for your channel, please switch to the Broadcaster Dashboard.',
            'link' => 'Go to Broadcaster Dashboard',
        ],
        'data-export' => [
            'heading' => 'Your Data',
            'subheading' => 'Download a copy of your data',
            'description' => 'Download a copy of all personal data we have stored about your account.',
            'confirmation' => 'Confirm using your Two-Factor Authentication',
            'submit' => 'Download My Data',
        ],
        'delete' => [
            'heading' => 'Delete Account',
            'subheading' => 'Are you sure you want to delete your account?',
            'description' => 'Your broadcaster profile and personal data will be deleted. For technical reasons, your Twitch user ID, votes you have cast until they are archived, and clips of other channels you have submitted will be retained. However, clips from your own channel will be hidden immediately and will only become visible again if you create a new broadcaster profile in the future.',
            'confirmation' => [
                'two-factor' => [
                    'label' => 'Confirm using your Two-Factor Authentication',
                ],
                'keyword' => [
                    'label' => 'Confirm by typing <code>:keyword</code>',
                    'keyword' => 'DELETE ACCOUNT',
                ],
            ],
            'submit' => 'Delete Account',
        ],
    ],
    'statistics' => [
        'title' => 'Account Statistics',
        'heading' => 'Your Statistics',
        'subheading' => '',
        'description' => '',
        'stats' => [
            'clips-submitted' => [
                'title' => 'Submitted Clips',
                'details' => 'View Submitted Clips',
                'stats' => [
                    'total' => 'Total',
                    'week' => 'This Week',
                ],
            ],
            'votes' => [
                'title' => 'Votes',
                'details' => 'View Votes',
                'stats' => [
                    'total' => 'Total',
                    'week' => 'This Week',
                    '30days' => 'Last 30 Days',
                ],
            ],
        ],
    ],
    'statistics_details' => [
        'infinite-loader' => [
            'no-more' => 'There are no more :name',
            'nothing-found' => 'No :name found',
            'nothing-found-subtext' => 'There are no :name available right now.',
        ],
        'votes' => [
            'name' => 'Votes',
            'heading' => 'Your Votes',
            'subheading' => '',
            'description' => '',
        ],
    ],
];
