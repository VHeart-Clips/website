<?php

declare(strict_types=1);

return [
    'sections' => [
        'consent' => [
            'label' => 'Manage Consent',
            'description' => 'Changes may not take effect in the next episode if you revoke between Thu 00:00 and Fri 16:00.',
        ],
        'default_clip_status' => [
            'label' => 'Default Clip Status',
            'description' => 'Unrelated to who can submit clips, how would you like us to handle your Clips?',
        ],
        'submissions_settings' => [
            'label' => 'Manage Submissions',
            'description' => 'Who should be able to submit your Clips? We recommend to open it for everyone.',
            'form' => [
                'submit_user_allowed' => [
                    'label' => 'Everyone',
                    'description' => 'Everyone will be able to Submit your clips.',
                ],
                'submit_vip_allowed' => [
                    'label' => 'VIPs',
                    'description' => 'VIPs will be able to Submit your clips.',
                ],
                'submit_mods_allowed' => [
                    'label' => 'Moderators',
                    'description' => 'Moderators will be able to Submit your clips.',
                ],
            ],
        ],
    ],
    'actions' => [
        'apply_default_status' => [
            'label' => 'Apply',
            'modal' => [
                'heading' => 'Apply Default Status to Clips',
                'description' => 'Overwrite clip statuses with your current default (:status).',
                'submit' => 'Apply',
                'helper_text' => 'All selected clips will be updated to your current default status. This cannot be undone automatically. Clips attached to a Compilation are always excluded.',
            ],
            'notifications' => [
                'none_matched' => 'No clips matched the selected statuses',
                'success' => [
                    'title' => ':count clip updated|:count clips updated',
                    'body' => ':count clip has been updated to :status|:count clips have been updated to :status',
                ],
                'rate_limited' => [
                    'title' => 'Slow down',
                    'body' => 'Try again in :seconds seconds.',
                ],
            ],
        ],
    ],
];
