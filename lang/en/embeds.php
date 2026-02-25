<?php

declare(strict_types=1);

return [
    'generic' => [
        'consent' => [
            'text' => 'External Content has been Blocked for your Privacy',
            'button' => 'Accept & Load Content',
            'link-text' => 'Open in new Tab',
        ],
        'invalid' => [
            'text' => 'Invalid Embed Link',
        ],
        'noscript' => [
            'text' => 'Javascript is required to see this content.',
        ],
    ],
    'youtube' => [
        'consent' => [
            'text' => 'By loading the video, you accept YouTube\'s privacy policy and the transfer of data to the USA. Depending on your cookie settings, your choice will be saved.',
            'button' => 'Accept & Load Video',
            'privacy-policy' => 'YouTube Privacy Policy',
        ],
    ],
    'twitch' => [
        'consent' => [
            'text' => 'By loading the clip, you accept Twitch\'s privacy policy and the transfer of data to the USA. Depending on your cookie settings, your choice will be saved.',
            'button' => 'Accept & Load Clip',
            'privacy-policy' => 'Twitch Privacy Policy',
        ],
    ],
];
