<?php

return [
    'page_title' => 'Submit clip',
    'breadcrumb' => 'Submit clip',
    'headline' => 'Submit clip',

    'preview' => [
        'title' => 'Preview',
        'placeholder' => 'Preview will be shown here',
        'loading' => 'Checking…',
    ],

    'submit' => [
        'title' => 'Submit',
        'clip_url_label' => 'Clip URL *',
        'clip_url_placeholder' => 'https://www.twitch.tv/<name>/clip/<id> or https://clips.twitch.tv/<id>',
        'tags_label' => 'Tags',
        'tags_placeholder' => 'Select tags…',
        'tags_filter_placeholder' => 'Filter tags…',
        'tags_selected_count' => '{{count}} of {{max}} selected',
        'tags_no_results' => 'No tags found.',
        'tags_max_error' => 'You can select up to {{max}} tags.',
        'tags_remove_label' => 'Remove tag {{tag}}',
        'anonymous' => 'Submit anonymously',
        'anonymous_hint' => '(Your name will not be shown publicly)',
        'cta' => 'Submit clip',
    ],

    'rules' => [
        'title' => 'Guidelines',
        'items' => [
            'registered' => 'Clip must be from a registered broadcaster',
            'consent' => 'Broadcaster must have approved usage and submissions',
            'no_explicit' => 'No explicit or offensive content',
            'tags_match' => 'Tags must match the clip content',
        ],
    ],

    'tips' => [
        'title' => 'Tips for great clips',
        'items' => [
            'short' => 'Short, punchy moments',
            'quality' => 'Good audio and video quality',
            'funny' => 'Interesting or funny situations',
        ],
    ],

    'recent' => [
        'title' => 'Your latest submissions',
        'no_title' => 'Untitled',
        'open_clip' => 'Open clip',
        'views' => 'views',
        'empty_title' => 'No submissions yet',
        'empty_subtitle' => 'Your submitted clips will appear here',
    ],

    'status' => [
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
    ],

    'errors' => [
        'login_required' => 'Please log in',
        'clip_not_found' => 'Clip not found',
        'clip_already_known' => 'This clip has already been submitted',
        'broadcaster_not_allowed' => 'The broadcaster has not allowed clip submissions',
        'game_blocked' => 'Clips from this game are not allowed',
        'user_not_allowed_for_broadcaster' => 'You are not allowed to submit clips for this broadcaster',
        'clip_url_required' => 'Please enter a clip URL',
        'cannot_submit' => 'This clip cannot be submitted.',
        'daily_limit' => 'You have reached your daily limit of :limit submissions.',
        'generic' => 'Something went wrong. Please try again.',
    ],

    'login' => [
        'title' => 'Login required',
        'subtitle' => 'You must be logged in to submit clips',
        'alert' => 'Only logged-in users can submit clips to prevent spam.',
        'cta' => 'Log in now',
    ],

    'flash' => [
        'submitted' => 'Your clip has been submitted successfully!',
    ],
];
