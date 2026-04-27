<?php

declare(strict_types=1);

return [
    'title' => 'Broadcaster Onboarding',
    'heading' => 'Welcome, :username!',
    'setup' => [
        'heading' => 'Lets setup your profile.',
        'consent' => [
            'heading' => 'Content Permission',
            'subheading' => 'We need your consent to be able to use and accept your content for our Compilations, you don\'t have to grant them now though if you just want to look around.',
        ],
        'default_clip_status' => [
            'heading' => 'Default Clip Status',
            'subheading' => 'Unrelated to who can submit clips, how would you like us to handle your Clips?',
            'options' => [
                'approved' => 'Clips can be used for Compilations as soon as they are submitted',
                'need_approval' => 'Clips require your approval from your dashboard before we can use them for Compilations',
            ],
        ],
        'submissions' => [
            'heading' => 'Clip Submissions',
            'subheading' => 'Who should be able to submit your Clips? We recommend to open it for everyone.',
            'options' => [
                'everyone' => [
                    'label' => 'Everyone',
                    'description' => 'Everyone will be able to Submit your clips.',
                ],
                'vips' => [
                    'label' => 'VIPs',
                    'description' => 'VIPs will be able to Submit your clips.',
                ],
                'mods' => [
                    'label' => 'Moderators',
                    'description' => 'Moderators will be able to Submit your clips.',
                ],
            ],
        ],
        'later' => 'I will decide Later',
        'submit' => 'Save and Continue',
    ],
    'alert' => [
        'heading' => 'Streamer?',
        'description' => 'Please set up your profile and grant us permission to use your content.',
        'cta' => 'Set up your profile',
        'dismiss' => 'Dismiss notice',
    ],
];
