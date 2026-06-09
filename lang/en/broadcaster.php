<?php

declare(strict_types=1);

return [
    'ban' => [
        'heading' => [
            'temporary' => ':name is Temporarily Banned',
            'permanent' => ':name is Permanently Banned',
        ],
        'description' => 'Clip submissions are disabled for this channel.',
        'temporary' => 'Ban lifts on :date.',
        'permanently' => 'This ban has no expiry date.',
        'any-questions' => 'Have questions?',
        'discord' => 'Open a ticket in our Discord.',
    ],
    'enums' => [
        'broadcaster-consent' => [
            'compilations' => 'Compilations',
            'compilations_description' => 'Allows us to use your content for Compilations',
            'shorts' => 'Shorts',
            'shorts_description' => 'Allows us to use your content for Shorts',
        ],
        'dashboard-navigation-group' => [
            'settings' => 'Settings',
        ],
        'dashboard-navigation-item' => [
            'general-settings' => 'General Settings',
            'manage-user-filter' => 'Manage User Filter',
            'manage-category-filter' => 'Manage Category Filter',
            'manage-team-member' => 'Manage Team Member',
            'removal-requests' => 'Removal Request',
        ],
        'broadcaster-permission' => [
            'clips' => 'Clips',
            'submissions-setting' => 'Submission Settings',
            'category-filter' => 'Category Filter',
            'user-filter' => 'User Filter',
            'removal-requests' => 'Removal Requests',
        ],
        'broadcaster-permission-description' => [
            'clips' => 'View/Edit Clips',
            'submissions-setting' => 'View/Edit Submission Settings',
            'category-filter' => 'View/Edit Kategorie Filter',
            'user-filter' => 'View/Edit User Filter',
            'removal-requests' => 'Manage your Removal Requests',
        ],
        'removal-request-status' => [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ],
    ],
];
