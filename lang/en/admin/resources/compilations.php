<?php

declare(strict_types=1);

return [
    'form' => [
        'title' => 'Title',
        'slug' => 'Slug',
        'description' => 'Description',
        'youtube_url' => 'Youtube URL',
        'created_by' => 'Created By',
        'status' => 'Status',
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
                'status_cutter' => 'Cutter Status',
                'status_moderation' => 'Moderation Status',
                'removed_at' => 'Removed At',
            ],
            'filters' => [
                'broadcaster' => 'Broadcaster',
                'creator' => 'Clip Creator',
                'submitter' => 'Submitter',
                'claimer' => 'Claimer',
                'claimer_option_none' => 'None / Unclaimed',
                'category' => 'Category',
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
                'claim_override' => [
                    'heading' => 'Already Claimed',
                    'description' => 'Are you sure you want to claim this clip?',
                ],
                'unclaim' => 'Unclaim',
                'copy_filename' => 'Copy Filename',
                'copy_filename_tooltip' => 'Copy standardized filename for editors',
                'status' => [
                    'title' => 'Update Status',
                ],
            ],
            'notifications' => [
                'claimed' => [
                    'title' => 'Clip Claimed',
                    'body' => 'You have successfully claimed this clip.',
                ],
                'unclaimed_title' => 'Clip Unclaimed',
                'download_error_title' => 'Cannot download Clip',
                'download_error_broadcaster' => 'Broadcaster is not available.',
                'download_error_not_found' => 'Clip was not found.',
                'filename_copied' => 'Filename Copied',
                'status_updated' => 'Status has been updated',
            ],
        ],
    ],
    'action' => [
        'generate-slug' => 'Generate Slug from Title',
    ],
];
