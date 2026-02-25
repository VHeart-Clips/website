<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\ExternalContentProxyType;
use App\Models\Category;
use App\Models\Clip;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('it correctly resolves resource url from database', function () {
    $clip = Clip::factory()->create([
        'twitch_id' => 'some-slug',
        'thumbnail_url' => 'https://example.com/img.jpg',
    ]);

    $clip->broadcaster()->update([
        'clip_permission' => true,
    ]);

    $resolvedUrl = ExternalContentProxyType::TwitchClip->getResource('some-slug');
    expect($resolvedUrl)->toBe('https://example.com/img.jpg');
});

test('it resolves and replaces dynamic dimensions for categories', function () {
    Category::factory()->create([
        'id' => 123,
        'box_art' => 'https://example.com/box-{width}x{height}.jpg',
    ]);

    $resolvedWithDims = ExternalContentProxyType::TwitchCategory->getResource('123-200x300');
    expect($resolvedWithDims)->toBe('https://example.com/box-200x300.jpg');

    $resolvedRaw = ExternalContentProxyType::TwitchCategory->getResource('123');
    expect($resolvedRaw)->toBe('https://example.com/box-{width}x{height}.jpg');
});

test('it throws model not found exception if identifier does not exist', function () {
    ExternalContentProxyType::TwitchUser->getResource('999999');
})->throws(ModelNotFoundException::class);
