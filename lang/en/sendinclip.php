<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

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
];
