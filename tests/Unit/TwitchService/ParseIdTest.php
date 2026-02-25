<?php

declare(strict_types=1);

use App\Services\Twitch\TwitchService;
use Tests\TestCase;

uses(TestCase::class); // should probably refactor the service tbh

test('correctly parses clip ids from any input', function (string $input, ?string $expectedOutput): void {
    $twitchService = new TwitchService();

    $parsedId = $twitchService->parseClipId($input);

    expect($parsedId)->toBe($expectedOutput);
})->with([
    // Valid
    [
        'input' => 'https://clips.twitch.tv/embed?clip=AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-&parent=example.com',
        'expectedOutput' => 'AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
    ],
    [
        'input' => 'https://www.twitch.tv/lirik/clip/AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
        'expectedOutput' => 'AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
    ],
    [
        'input' => 'https://www.twitch.tv/lirik/clip/AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-?filter=clips&range=30d&sort=time',
        'expectedOutput' => 'AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
    ],
    [
        'input' => 'https://clips.twitch.tv/AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
        'expectedOutput' => 'AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
    ],
    [
        'input' => 'AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
        'expectedOutput' => 'AttractiveAgreeableOxNononoCat-rI2mENW1153C9KU-',
    ],
    // Invalid, should return null
    ['https://www.twitch.tv/videos/123456789', null],
    ['https://www.twitch.tv/shroud', null],
    ['https://www.twitch.tv/riot-games', null],
    ['https://clips.twitch.tv/', null],
    ['never gonna give you up', null],
    ['', null],
    ['https://clips.twitch.tv/IncompleteSlugHere', null],
]);
