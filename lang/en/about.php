<?php

declare(strict_types=1);

return [
    'page_title' => 'About us',
    'breadcrumb' => 'About us',
    'back' => 'Back',

    'hero' => [
        'title_prefix' => 'WELCOME TO',
        'brand' => 'VHEART',
        'description' => 'VHeart is a collective of editors, streamers and artists who created a high-quality clip compilation for a good cause!',
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
                'description' => 'The jury has a vote that is weighted more heavily than the community vote.',
            ],
            'moderation' => [
                'title' => 'Moderation',
                'description' => 'Our moderators take care of the report system and are available on our Discord server if you have any questions!',
            ],
            'edit' => [
                'title' => 'Final Editing',
                'description' => 'Our editors review the top-rated clips and select one of them to edit.',
            ],
        ],
        'neutrality' => 'This ensures nothing is out of context, nobody is misrepresented and/or clips are not selected subjectively. We want to approach the selection as neutrally as possible and therefore chose this process.',
        'blacklist' => 'Additionally, clips can be blacklisted by creators or their mods — meaning they are completely excluded!',
    ],
];
