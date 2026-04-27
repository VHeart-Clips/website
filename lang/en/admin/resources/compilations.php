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

            'clips_count' => 'Total Clips',
            'clips_count_pending' => 'Pending',
            'clips_count_in_progress' => 'In Progress',
            'clips_count_completed' => 'Completed',
            'clips_sum_duration' => 'Total Duration',
            'clips_avg_duration' => 'Avg Duration',
            'clips_est_duration' => 'Est. Length',

            'progress' => 'Progress',
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
                'adder' => 'Added By',
                'claimer' => 'Claimer',
                'status_cutter' => 'Cutter Status',
                'status_moderation' => 'Moderation Status',
                'removed_at' => 'Removed At',
                'added_at' => 'Added At',
            ],
            'filters' => [
                'broadcaster' => 'Broadcaster',
                'creator' => 'Clip Creator',
                'submitter' => 'Submitter',
                'adder' => 'Added By',
                'adder_option_none' => 'Nobody',
                'claimer' => 'Claimer',
                'claimer_option_none' => 'None / Unclaimed',
                'category' => 'Category',
                'cutter_status' => 'Cutter Status',
                'clip_status' => 'Clip Status',
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
                'move_to_compilation' => 'Move to Compilation',
                'status' => [
                    'title' => 'Update Status',
                ],
                'attach_clip' => [
                    'status' => 'Status',
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
                'filename_copy_failed_title' => 'Could not copy filename',
                'filename_copy_failed_no_broadcaster' => 'Broadcaster profile not found',
                'status_updated' => 'Status has been updated',
                'readonly' => 'This Compilation is scheduled or published and can only be updated by its owner.',
                'moved_to_compilation' => 'Successfully moved to compilation.',
            ],
        ],
    ],
    'action' => [
        'generate-slug' => 'Generate Slug from Title',
    ],
];
