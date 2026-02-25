<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\ExternalContentProxyType;
use App\Models\Category;
use App\Models\Clip;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('it successfully proxies a valid image resource', function () {
    User::factory()->create(['id' => 1, 'avatar_url' => 'https://example.com/avatar.png']);

    Http::fake([
        'https://example.com/avatar.png' => Http::response('fake-image-content', 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => 18,
            'ETag' => 'abc-123',
        ]),
    ]);

    $response = $this->get(route('static-external', [
        'type' => 'user',
        'identifier' => '1',
        'extension' => 'png',
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Cache-Control', 'immutable, max-age=31536000, public, s-maxage=31536000');

    expect($response->streamedContent())->toBe('fake-image-content');
});

test('it aborts with 404 if the external resource fails to load', function () {
    User::factory()->create(['id' => 1, 'avatar_url' => 'https://example.com/broken.png']);

    Http::fake([
        'https://example.com/broken.png' => Http::response(null, 404),
    ]);

    $this->get(route('static-external', [
        'type' => 'user',
        'identifier' => '1',
        'extension' => 'png',
    ]))->assertNotFound();
});

test('it aborts with 415 if external content is not an image', function () {
    User::factory()->create(['id' => 1, 'avatar_url' => 'https://example.com/malicious.exe']);

    Http::fake([
        'https://example.com/malicious.exe' => Http::response('executable-code', 200, [
            'Content-Type' => 'application/x-msdownload',
        ]),
    ]);

    $this->get(route('static-external', [
        'type' => 'user',
        'identifier' => '1',
        'extension' => 'png',
    ]))->assertStatus(415);
});

test('it handles dynamic size requests in the controller', function () {
    Category::factory()->create([
        'id' => 50,
        'box_art' => 'https://example.com/category-{width}x{height}.jpg',
    ]);

    Http::fake([
        'https://example.com/category-100x200.jpg' => Http::response('small-cover', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $response = $this->get(route('static-external', [
        'type' => 'category',
        'identifier' => '50-100x200',
        'extension' => 'jpg',
    ]));

    $response->assertOk();
    expect($response->streamedContent())->toBe('small-cover');
});

test('it returns 404 if the local model does not exist', function () {
    $this->get(route('static-external', [
        'type' => 'user',
        'identifier' => '1',
        'extension' => 'png',
    ]))->assertNotFound();
});

test('it returns 404 if the local clip exists but broadcaster gave no permission', function () {
    $clip = Clip::factory()->create([
        'thumbnail_url' => 'https://example.com/thumbnail.jpg',
    ]);

    Http::fake();

    $this->get(route('static-external', [
        'type' => ExternalContentProxyType::TwitchClip,
        'identifier' => $clip->twitch_id,
        'extension' => 'jpg',
    ]))->assertNotFound();

    Http::assertNothingSent();
});

test('it successfully proxies if the local clip exists and broadcaster gave permission', function () {
    $clip = Clip::factory()->create([
        'thumbnail_url' => 'https://example.com/thumbnail.jpg',
    ]);
    $clip->broadcaster->update([
        'clip_permission' => true,
    ]);

    Http::fake([
        'https://example.com/thumbnail.jpg' => Http::response('fake-image-content', 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => 18,
        ]),
    ]);

    $response = $this->get(route('static-external', [
        'type' => ExternalContentProxyType::TwitchClip,
        'identifier' => $clip->twitch_id,
        'extension' => 'jpg',
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Cache-Control', 'immutable, max-age=31536000, public, s-maxage=31536000');
});

test('it generates correct proxy url for standard models', function () {
    $clip = Clip::factory()->create(['twitch_id' => 'clipper123']);
    $user = User::factory()->create(['id' => 55]);

    $clipUrl = $clip->proxiedContentUrl();
    $userUrl = $user->proxiedContentUrl();

    expect($clipUrl)->toContain('/static-external/clip/clipper123.jpg')
        ->and($userUrl)->toContain('/static-external/user/55.png');
});

test('it generates dynamic size urls for categories', function () {
    $game = Category::factory()->create(['id' => 999]);

    $urlResized = $game->proxiedContentUrl(50, 50);
    $urlStandard = $game->proxiedContentUrl();

    expect($urlResized)->toContain('/static-external/category/999-50x50.jpg')
        ->and($urlStandard)->toContain('/static-external/category/999.jpg');
});
