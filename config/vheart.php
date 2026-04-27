<?php

declare(strict_types=1);

use Carbon\CarbonInterval;

return [
    'clips' => [
        'submission' => [
            // Minimum Clip length we accept at submission
            'minimum_length' => (int) env('VHEART_CLIPS_SUBMISSION_MINIMUM_LENGTH', 5),
            // Maximum Clip age we accept at submission
            'maximum_age' => CarbonInterval::fromString((string) env('VHEART_CLIPS_SUBMISSION_MAXIMUM_AGE', '6 months')),

            'limits' => [
                'total' => (int) env('VHEART_CLIPS_SUBMISSION_LIMITS_TOTAL', 20),
                'per_broadcaster' => (int) env('VHEART_CLIPS_SUBMISSION_LIMITS_BROADCASTER', 5),
            ],
        ],
        'voting' => [
            'maximum_age' => CarbonInterval::fromString((string) env('VHEART_CLIPS_VOTING_MAXIMUM_AGE', '6 months')),

            // I think 4 gives a good balance by default, there is basically no boost if a clip has about 50% as much as interactions
            // as the clip with the most, we can still configure later though if we need to.
            // https://www.desmos.com/calculator/pu5hu8bpev
            'interaction_boost_exponent' => (int) env('VHEART_CLIPS_VOTING_INTERACTION_BOOST_EXPONENT', 4),
        ],
        'scoring' => [
            'jury_weight' => (int) env('VHEART_CLIPS_SCORING_JURY_WEIGHT', 10),
            'public_weight' => (int) env('VHEART_CLIPS_SCORING_PUBLIC_WEIGHT', 1),
        ],
    ],
];
