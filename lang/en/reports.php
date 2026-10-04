<?php

declare(strict_types=1);

return [
    'enums' => [
        'report-status' => [
            'pending' => 'Pending',
            'in-review' => 'In Review',
            'resolved' => 'Resolved',
            'dismissed' => 'Dismissed',
        ],
        'report-reason' => [
            'other' => 'Other',
            'spam' => 'Spam',
            'harassment' => 'Harassment',
            'hate-speech' => 'Hate Speech',
            'ai-content' => 'AI Content',
            'content-unavailable' => 'Content Unavailable',
        ],
        'report-category-details-type' => [
            'disabled' => 'Disabled',
            'optional' => 'Optional',
            'required' => 'Required',
        ],
        'resolve-action' => [
            'other' => 'Other',
            'dismissed' => 'Report Dismissed',
            'content-edited' => 'Content Edited',
            'content-removed' => 'Content Removed',
            'user-banned' => 'User Banned',
        ],
    ],
    'modal' => [
        'title' => 'Report :reportable',
        'subtitle' => 'Please provide details about the issue you encountered.',
        'inputs' => [
            'reason' => [
                'label' => 'Reason',
            ],
            'description' => [
                'label' => 'Additional Details',
                'placeholder' => 'Required if "Other" is selected.',
            ],
            'cancel' => 'Cancel',
            'submit' => 'Submit Report',
        ],
        'success' => [
            'title' => 'Report successfully submitted',
            'message' => 'Thank you, your report has been submitted.',
            'report-id' => 'Please provide the following ID for related questions: ',
            'ok' => 'Okay',
        ],
        'button' => 'Report :reportable',
    ],
    'reportable' => [
        'user' => 'User',
        'clip' => 'Clip',
    ],
];
