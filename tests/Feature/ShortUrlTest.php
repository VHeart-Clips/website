<?php

declare(strict_types=1);

use App\Models\Clip\Compilation;
use App\Models\ShortUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('redirects', function () {
    it('resolves url slug', function () {
        $short = ShortUrl::factory()->create();
        $this->get(route('shorturl.redirect', ['slug' => $short->slug]))
            ->assertRedirect($short->url);
    });

    it('resolves any valid slug', function () {
        $short = ShortUrl::factory()->create(['slug' => 'path/to/something']);
        $this->get(route('shorturl.redirect', ['slug' => $short->slug]))
            ->assertRedirect($short->url);
    });

    it('resolves compilation slug', function () {
        $compilation = Compilation::factory()->create(['youtube_url' => 'https://example.com']);
        $this->get(route('shorturl.redirect', ['slug' => $compilation->slug]))
            ->assertRedirect($compilation->youtube_url);
    });

    it('falls back to home on unknown slug', function () {
        $this->get(route('shorturl.redirect', ['slug' => 'invalid']))
            ->assertRedirect(route('home'));
    });

    it('rickroll', function () {
        $this->get(route('shorturl.redirect', ['slug' => 'episode-0']))
            ->assertRedirect('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    });

    it('falls back to home on root access (by default at least)', function () {
        $this->get(route('shorturl.redirect'))
            ->assertRedirect(route('home'));
    });
});

describe('click tracking', function () {
    it('increments on visit', function () {
        $short = ShortUrl::factory()->create();

        $this->get(route('shorturl.redirect', ['slug' => $short->slug]));

        expect($short->refresh()->clicks)->toBe(1);
    });

    it('does not increment twice for same ip', function () {
        $short = ShortUrl::factory()->create();

        $this->get(route('shorturl.redirect', ['slug' => $short->slug]), ['REMOTE_ADDR' => '1.1.1.1']);
        $this->get(route('shorturl.redirect', ['slug' => $short->slug]), ['REMOTE_ADDR' => '1.1.1.1']);

        expect($short->refresh()->clicks)->toBe(1);
    });

    it('increments for different ips', function () {
        $short = ShortUrl::factory()->create();

        $this->get(route('shorturl.redirect', ['slug' => $short->slug]), ['REMOTE_ADDR' => '1.1.1.1']);
        $this->get(route('shorturl.redirect', ['slug' => $short->slug]), ['REMOTE_ADDR' => '2.2.2.2']);

        expect($short->refresh()->clicks)->toBe(2);
    });

    it('increments again after cache expires', function () {
        $short = ShortUrl::factory()->create();

        $this->get(route('shorturl.redirect', ['slug' => $short->slug]), ['REMOTE_ADDR' => '1.1.1.1']);
        $this->travel(1)->hour();
        $this->get(route('shorturl.redirect', ['slug' => $short->slug]), ['REMOTE_ADDR' => '1.1.1.1']);

        expect($short->refresh()->clicks)->toBe(2);
    });
});
