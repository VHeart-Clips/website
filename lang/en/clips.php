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
                    'max_age' => 'Clips must not be older than :age.',
                    'minimum_duration' => 'Clips must be at least :duration seconds.',
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

    'vote' => [
        'page_title' => 'Vote clips',
        'form' => [
            'fields' => [
                'vote' => [
                    'label' => 'Vote for this Clip',
                ],
                'skip' => [
                    'label' => 'Skip this Clip',
                ],
            ],
        ],
        'aside' => [
            'total_votes' => 'Total votes for this Clip so far',
            'nothing_left' => 'We can\'t find any Clips for you to vote. Please come back later.',
        ],
    ],

    'enums' => [
        'clip-status' => [
            'unknown' => 'Unknown',
            'need-approval' => 'Need Approval',
            'approved' => 'Approved',
            'blocked' => 'Blocked',
        ],
        'clip-feedback-option' => [
            'audio-too-quiet' => 'Audio too quiet',
            'audio-too-loud' => 'Audio too loud',
            'bad-audio-quality' => 'Bad Audio Quality',
            'bad-video-quality' => 'Bad Video Quality',
            'content-unavailable' => 'Content is not available anymore',
            'other' => 'Other',
        ],
    ],

    'preview' => [
        'consent-required' => 'Requires external services consent to view this preview.',
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
        'too_old' => 'This clip is too old. We only accept clips that are less than :age old.',
        'too_short' => 'This clip is too short. Clips must be at least :seconds seconds long.',
        'total_limit_reached' => 'You have hit today\'s limit, try again tomorrow.',
        'broadcaster_limit_reached' => 'You have hit today\'s limit for this broadcaster, try again tomorrow.',
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
