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
        'status' => 'Status',
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
            'votes' => 'Votes',
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
        'status' => 'Status',
        'status_visibility' => [
            'label' => 'Status',
            'placeholder' => 'All',
            'true' => 'Blocked Only',
            'false' => 'Approved Only',
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
        'view_on_twitch' => 'View On Twitch',
    ],
    'notifications' => [],
];
