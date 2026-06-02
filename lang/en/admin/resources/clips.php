<?php

declare(strict_types=1);

return [
    'form' => [
        'title' => 'Title',
        'twitch_category' => 'Twitch Category',
        'duration' => 'Duration',
        'broadcaster' => 'Broadcaster',
        'creator' => 'Clip Creator',
        'submitted_by' => 'Submitted By',
        'category' => 'Category',
        'created_at' => 'Clip Created At',
        'tags' => 'Tags',
    ],
    'infolist' => [
        'title' => 'Title',
        'category' => 'Category',
        'duration' => 'Duration',
        'broadcaster' => 'Broadcaster',
        'creator' => 'Clip Creator',
        'submitted_by' => 'Submitted By',
        'submitted_at' => 'Submitted At',
        'created_at' => 'Clip Created At',
    ],
    'table' => [
        'columns' => [
            'twitch_id' => 'Twitch ID',
            'thumbnail' => 'Thumbnail',
            'title' => 'Title',
            'broadcaster' => 'Broadcaster',
            'creator' => 'Clip Creator',
            'submitter' => 'Submitter',
            'category' => 'Category',
            'duration' => 'Duration',
            'status' => 'Status',
            'jury_votes' => 'Jury Votes',
            'public_votes' => 'Public Votes',
            'absolute_votes' => 'Votes',
            'absolute_impressions' => 'Impressions',
            'score' => 'Clip Score',
            'submitted_at' => 'Submitted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ],
    ],
    'filters' => [
        'broadcaster' => 'Broadcaster',
        'creator' => 'Clip Creator',
        'submitter' => 'Submitter',
        'category' => 'Category',
        'tags' => 'Tags',
        'in_compilation' => [
            'label' => 'Compilations',
            'only_without_compilation' => 'Clips without Compilation',
            'only_with_compilation' => 'Clips with Compilation',
            'with_compilation' => 'All Clips',
        ],
        'status' => 'Status',
        'status_visibility' => [
            'label' => 'Status',
            'placeholder' => 'Approved Only',
            'true' => 'Blocked Only',
            'false' => 'All',
        ],

        'has_consent_simple' => [
            'label' => 'Consent (Simple)',
            'options' => [
                'default' => 'Only clips with Consent',
                'true' => 'Only clips without Consent',
                'false' => 'All Clips with or without Consent',
            ],
        ],

        'has_consent' => [
            'label' => 'Consent',
        ],

        'created_range' => [
            'label' => 'Created Between',
            'indicator' => 'Clip Created',
        ],

        'submission_range' => [
            'label' => 'Submitted Between',
            'indicator' => 'Clip Submitted',
        ],
    ],
    'edit' => [
        'title' => 'Edit :label by :broadcaster',
    ],
    'actions' => [
        'download' => 'Open Downloadable Clip',
        'view_on_twitch' => 'View On Twitch',
        'attach_to_compilation' => [
            'label' => 'Attach to Compilation',
            'claim' => 'Claim Clip',
            'status' => 'Clip Status',
        ],
    ],
    'notifications' => [
        'actions' => [
            'attached_to_compilation' => 'Attached to Compilation',
        ],
        'submit_error' => [
            'title' => 'Could not import clip',
            'body' => 'An unexpected error occurred. Please try again. If the problem persists, try logging out and back in.',
        ],
    ],
];
