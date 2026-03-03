<?php

declare(strict_types=1);

return [
    'submit' => [
        'page_title' => 'Submit clip',
        'preview' => [
            'heading' => 'Preview',
        ],
        'form' => [
            'heading' => 'Submit clip',
            'fields' => [
                'clip_url' => [
                    'label' => 'Clip URL*',
                    'placeholder' => 'https://www.twitch.tv/<name>/clip/<id>',
                ],
                'tags' => [
                    'label' => 'Tags',
                    'placeholder' => 'Select tags...',
                    'description' => 'Select up to 3 tags (minimum 1).',
                    'filter_placeholder' => 'Filter tags…',
                    'selected_count' => ':count of :max selected',
                    'no_results' => 'No tags found.',
                    'max_error' => 'You can select up to :max tags.',
                    'remove_label' => 'Remove tag :tag',
                ],
            ],
            'submit' => 'Submit',
        ],
        'aside' => [
            'rules' => [
                'heading' => 'Guidelines',
                'items' => [
                    'registered' => 'Clip must be from a registered broadcaster',
                    'consent' => 'Broadcaster must have approved usage and submissions',
                    'no_explicit' => 'No explicit or offensive content',
                    'tags_match' => 'Tags must match the clip content',
                ],
            ],
            'tips' => [
                'heading' => 'Tips for great clips',
                'items' => [
                    'short' => 'Short, punchy moments',
                    'quality' => 'Good audio and video quality',
                    'funny' => 'Interesting or funny situations',
                ],
            ],
        ],
    ],

    // Untouched

    'recent' => [
        'title' => 'Your latest submissions',
        'no_title' => 'Untitled',
        'open_clip' => 'Open clip',
        'views' => 'views',
        'empty_title' => 'No submissions yet',
        'empty_subtitle' => 'Your submitted clips will appear here',
    ],

    'login' => [
        'title' => 'Login required',
        'subtitle' => 'You must be logged in to submit clips',
        'alert' => 'Only logged-in users can submit clips to prevent spam.',
        'cta' => 'Log in now',
    ],

    'errors' => [
        'login_required' => 'Please log in',
        'clip_not_found' => 'Clip not found',
        'clip_already_known' => 'This clip has already been submitted',
        'broadcaster_not_allowed' => 'The broadcaster has not allowed clip submissions',
        'category_blocked' => 'Clips from this Category/Game are not allowed',
        'user_not_allowed_for_broadcaster' => 'You are not allowed to submit clips for this broadcaster',
        'clip_url_required' => 'Please enter a clip URL',
        'cannot_submit' => 'This clip cannot be submitted.',
        'daily_limit' => 'You have reached your daily limit of :limit submissions.',
        'generic' => 'Something went wrong. Please try again.',
    ],

    'status' => [
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
    ],

    'flash' => [
        'submitted' => 'Your clip has been submitted successfully!',
    ],
];
