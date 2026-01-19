<?php

return [
    'form' => [
        'title' => 'Title',
        'slug' => 'Slug',
        'description' => 'Description',
        'youtube_url' => 'Youtube URL',
        'created_by' => 'Created By',
        'status' => 'Status',
        'auto_fill_seconds' => 'Auto Fill Seconds',
        'auto_fill_seconds_helper' => 'Automatically fill the Compilation with Clips to reach the set Minimum amount of Seconds, does nothing if left empty.',
        'type' => 'Type',
    ],
    'table' => [
        'columns' => [
            'title' => 'Title',
            'created_by' => 'Created By',
            'status' => 'Status',
            'type' => 'Type',
        ],
        'filters' => [
            'created_by' => 'Created By',
            'creation_date' => 'Filter by Creation Date',
            'youtube_link' => 'Youtube Link',
            'clips' => 'Clips',
            'with' => 'With',
            'without' => 'Without',
            'all' => 'All',
        ],
    ],
    'relation_managers' => [
        'clips' => [
            'columns' => [
                'claimer' => 'Claimer',
                'status' => 'Status',
                'removed_at' => 'Removed At',
            ],
            'filters' => [
                'broadcaster' => 'Broadcaster',
                'clipper' => 'Clipper',
                'submitter' => 'Submitter',
                'claimer' => 'Claimer',
                'claimer_option_none' => 'None / Unclaimed',
                'game' => 'Game',
                'status' => 'Status',
                'was_removed' => [
                    'label' => 'Removed',
                    'placeholder' => 'All',
                    'true' => 'Yes',
                    'false' => 'No',
                ],
            ],
            'actions' => [
                'claim' => 'Claim',
                'unclaim' => 'Unclaim',
                'copy_filename' => 'Copy Filename',
                'copy_filename_tooltip' => 'Copy standardized filename for editors',
            ],
            'notifications' => [
                'claimed_title' => 'Clip Claimed',
                'claimed_body' => 'You have successfully claimed this clip.',
                'unclaimed_title' => 'Clip Unclaimed',
                'download_error_title' => 'Cannot download Clip',
                'download_error_broadcaster' => 'Broadcaster is not available.',
                'download_error_not_found' => 'Clip was not found.',
                'filename_copied' => 'Filename Copied',
            ],
        ],
    ],
];
