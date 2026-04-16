<?php

declare(strict_types=1);

return [
    'page_title' => 'About us',
    'breadcrumb' => 'About us',
    'back' => 'Back',

    'hero' => [
        'title_prefix' => 'WELCOME TO',
        'brand' => 'VHEART',
        'description' => 'Vheart is a collective of editors, streamers and artists who created a high-quality clip compilation for a good cause!',
        'tags' => [
            'tag1' => '#forthesweeties',
            'tag2' => 'Charity Compilation',
            'tag3' => 'Community Voting',
        ],
    ],

    'clip_process' => [
        'title' => 'Clip Selection Process',
        'intro' => 'Wondering how we choose clips for our compilation? What happens after submitting a clip? And who is involved in the decision? We explain it here:',
        'steps' => [
            'community' => [
                'title' => 'Community Voting',
                'description' => 'As a community, you can vote for the funniest clips directly on this website.',
            ],
            'jury' => [
                'title' => 'Jury Role',
                'description' => '',
            ],
            'moderation' => [
                'title' => 'Moderation',
                'description' => '',
            ],
            'edit' => [
                'title' => 'Final Editing',
                'description' => '',
            ],
        ],
        'neutrality' => 'This ensures nothing is out of context, nobody is misrepresented and/or clips are not selected subjectively. We want to approach the selection as neutrally as possible and therefore chose this process.',
        'blacklist' => 'Additionally, clips can be blacklisted by creators or their mods — meaning they are completely excluded!',
    ],
];
