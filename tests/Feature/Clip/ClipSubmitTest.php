<?php

declare(strict_types=1);

use App\Actions\ImportClipAction;
use App\Models\Category;
use App\Models\Clip\Tag;
use App\Models\User;
use App\Services\Twitch\Data\ClipDto;
use App\Services\Twitch\TwitchService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;

beforeEach(function () {
    $this->submitter = User::factory()->create([
        'id' => 101000,
        'name' => 'Submitter',
    ]);

    $this->broadcaster = User::factory()
        ->withClipPermission()
        ->create([
            'id' => 201000, // Default for dto
            'name' => 'Broadcaster',
        ]);
    $this->broadcasterWithoutPermission = User::factory()
        ->withClipPermission(false)
        ->create([
            'id' => 201001,
            'name' => 'Disallowed',
        ]);

    $this->category = Category::factory()->create([
        'title' => 'Good Category',
        'id' => 1, // Default for dto
    ]);
    $this->bannedCategory = Category::factory()->isBanned()->create([
        'title' => 'Bad Category',
        'id' => 2,
    ]);

    $this->tags = Tag::factory()->count(5)->create();
    Gate::define('submit', static fn () => true);

    $this->clipUrl = 'https://www.twitch.tv/justplayerde/clip/GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb';
    $this->clipId = 'GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb';
    $this->clipDto = makeClipDto([
        'id' => $this->clipId,
        'url' => $this->clipUrl,
        'broadcasterId' => $this->broadcaster->id,
        'game_id' => $this->category->id,
    ]);

    Http::fake();
});

afterEach(function () {
    Http::assertNothingSent();
});

describe('input validation', function () {
    test('fails if the clip url is invalid', function (?string $invalidUrl) {
        assumeClipUrlToBeParsed(mockTwitchService(), $invalidUrl, null);

        $this->actingAs($this->submitter);

        $this
            ->post(route('submitclip.store'), [
                'clip_url' => $invalidUrl,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url']);
    })->with([
        'https://example.com',
        'https://clips.twitch.tv',
        'https://www.twitch.tv/justplayerde/clip/',
        'https://www.twitch.tv/justplayerde/clip/GentleNimblePanFreakinS tinkin-1eErSWrf0oTe4DNb',
        '',
        'hello',
        null,
    ]);

    test('passes with valid clip urls', function (string $url, string $id) {
        $dto = makeClipDto(['url' => $url, 'id' => $id]);
        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $url, $dto);
        assumeClipToBeRequested($mock, $dto);

        $this->mock(ImportClipAction::class)
            ->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->id === $dto->id),
                Mockery::on(fn ($arg) => $arg->id === $this->submitter->id),
                Mockery::type('array')
            );

        $this->actingAs($this->submitter);

        $this
            ->post(route('submitclip.store'), [
                'clip_url' => $url,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasNoErrors();
    })->with([
        ['url' => 'https://www.twitch.tv/justplayerde/clip/GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb', 'id' => 'GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb'],
        ['url' => 'https://clips.twitch.tv/GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb?tt_content=url&tt_medium=clips_api', 'id' => 'GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb'],
        ['url' => 'https://clips.twitch.tv/GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb', 'id' => 'GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb'],
        ['url' => 'https://clips.twitch.tv/embed?clip=GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb&parent=example.com', 'id' => 'GentleNimblePanFreakinStinkin-1eErSWrf0oTe4DNb'],
    ]);

    test('fails if user did not select any tags', function () {
        mockTwitchService()->shouldNotReceive('parseClipId');

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $this->clipUrl,
                'tags' => [],
            ])
            ->assertSessionHasErrors(['tags']);
    });
    test('fails if user selected more than 3 tags', function () {
        mockTwitchService()->shouldNotReceive('parseClipId');

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $this->clipUrl,
                'tags' => $this->tags->take(4)->pluck('id')->toArray(),
            ])
            ->assertSessionHasErrors(['tags']);
    });
});

describe('broadcaster requirements', function () {
    test('fails if the broadcaster is not registered on the site', function () {
        $invalidDto = makeClipDto(['broadcaster_id' => 999999]);
        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $invalidDto->url, $invalidDto);
        assumeClipToBeRequested($mock, $invalidDto);
        $mock->shouldNotReceive('get');

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $invalidDto->url,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.broadcaster_not_allowed')]);
    });

    test('fails if the broadcaster gave no permission for clips', function () {
        $disallowedDto = makeClipDto(['broadcaster_id' => $this->broadcasterWithoutPermission->id]);
        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $disallowedDto->url, $disallowedDto);
        assumeClipToBeRequested($mock, $disallowedDto);
        $mock->shouldNotReceive('get');

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $disallowedDto->url,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.broadcaster_not_allowed')]);
    });

    test('fails if user is blacklisted by broadcaster', function () {
        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $this->clipUrl, $this->clipDto);
        assumeClipToBeRequested($mock, $this->clipDto);
        $mock->shouldNotReceive('get');

        $this->broadcaster
            ->broadcasterFilter()
            ->create([
                'filterable_type' => $this->submitter->getMorphClass(),
                'filterable_id' => $this->submitter->id,
                'state' => false,
            ]);

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $this->clipUrl,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.user_not_allowed_for_broadcaster')]);
    });

    test('fails if category is blacklisted by broadcaster', function () {
        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $this->clipUrl, $this->clipDto);
        assumeClipToBeRequested($mock, $this->clipDto);
        $mock->shouldNotReceive('get');

        $this->broadcaster
            ->broadcasterFilter()
            ->create([
                'filterable_type' => new Category()->getMorphClass(),
                'filterable_id' => $this->category->id,
                'state' => false,
            ]);

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $this->clipUrl,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.category_blocked')]);
    });
});

describe('website requirements', function () {
    test('fails if the game category is site banned', function () {
        $dto = makeClipDto(['game_id' => $this->bannedCategory->id]);
        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $dto->url, $dto);
        assumeClipToBeRequested($mock, $dto);
        $mock->shouldNotReceive('get');

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $dto->url,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.category_blocked')]);
    });

    test('fails if clip is already known to us', function () {
        $this->broadcaster->broadcastedClips()
            ->create(
                $this->clipDto->toModel([
                    'submitter_id' => $this->broadcasterWithoutPermission->id,
                ])
            );

        $mock = mockTwitchService();
        assumeClipUrlToBeParsed($mock, $this->clipDto->url, $this->clipDto);
        assumeClipToBeRequested($mock, $this->clipDto);

        $this->mock(ImportClipAction::class)
            ->shouldReceive('execute')
            ->never()
            ->with(
                Mockery::on(fn (ClipDto $arg) => $arg->id === $this->clipDto->id),
                Mockery::on(fn (User $arg) => $arg->id === $this->submitter->id),
                Mockery::type('array')
            );

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $this->clipUrl,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.clip_already_known')]);
    });

    test('fails if clip does not exist on twitch', function () {
        $mock = mockTwitchService();
        $mock->shouldReceive('onUserTokenRefresh')->andReturnSelf();
        $mock->shouldReceive('asUser')->andReturnSelf();
        assumeClipUrlToBeParsed($mock, $this->clipUrl, $this->clipId);
        assumeClipToBeRequested($mock, $this->clipId);

        $this->actingAs($this->submitter)
            ->post(route('submitclip.store'), [
                'clip_url' => $this->clipUrl,
                'tags' => [$this->tags->first()->id],
            ])
            ->assertSessionHasErrors(['clip_url' => __('clips.errors.clip_not_found')]);
    });
});

test('should allow submission if everything is ok', function () {
    $mock = mockTwitchService();
    assumeClipUrlToBeParsed($mock, $this->clipDto->url, $this->clipDto);
    assumeClipToBeRequested($mock, $this->clipDto);

    $this->mock(ImportClipAction::class)
        ->shouldReceive('execute')
        ->once()
        ->with(
            Mockery::on(fn ($arg) => $arg->id === $this->clipDto->id),
            Mockery::on(fn ($arg) => $arg->id === $this->submitter->id),
            Mockery::type('array')
        );

    $this->actingAs($this->submitter)
        ->post(route('submitclip.store'), [
            'clip_url' => $this->clipUrl,
            'tags' => [$this->tags->first()->id],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('submitclip.create'))
        ->assertSessionHas('submit_ok', true)
        ->assertSessionHas('submit_message', __('clips.flash.submitted'));
});

function mockTwitchService(?ClipDto $dto = null): MockInterface
{
    $mock = Mockery::mock(TwitchService::class);
    $mock->shouldReceive('onUserTokenRefresh')->andReturnSelf();
    $mock->shouldReceive('asUser')->andReturnSelf();

    if ($dto) {
        $mock->shouldReceive('getClipByID')
            ->with($dto->id)
            ->andReturn($dto);
    }

    app()->instance(TwitchService::class, $mock);

    return $mock;
}

/**
 * assumes the given input url to be parsed and return the dto (id) or null
 */
function assumeClipUrlToBeParsed(MockInterface $mock, ?string $inputUrl, ClipDto|string|null $dtoOrId): MockInterface
{
    $mock->shouldReceive('parseClipId')->with($inputUrl)->andReturn($dtoOrId instanceof ClipDto ? $dtoOrId->id : $dtoOrId);

    return $mock;
}

/**
 * assume the given dto or id to be requested and return the dto or null
 */
function assumeClipToBeRequested(MockInterface $mock, ClipDto|string|null $dtoOrId): MockInterface
{
    $mock->shouldReceive('getClipById')->with($dtoOrId instanceof ClipDto ? $dtoOrId->id : $dtoOrId)->andReturn($dtoOrId instanceof ClipDto ? $dtoOrId : null);

    return $mock;
}

function makeClipDto(array $attributes = []): ClipDto
{
    return new ClipDto(
        id: $attributes['id'] ?? 'AwesomeClip',
        url: $attributes['url'] ?? 'https://clips.twitch.tv/AwesomeClip',
        embed_url: $attributes['embed_url'] ?? 'https://clips.twitch.tv/embed?clip=AwesomeClip',
        broadcaster_id: $attributes['broadcaster_id'] ?? 201000,
        broadcaster_name: $attributes['broadcaster_name'] ?? 'Broadcaster',
        creator_id: $attributes['creator_id'] ?? 101000,
        creator_name: $attributes['creator_name'] ?? 'Submitter',
        video_id: $attributes['video_id'] ?? 123,
        game_id: $attributes['game_id'] ?? 1,
        language: $attributes['language'] ?? 'en',
        title: $attributes['title'] ?? 'Cool Clip',
        view_count: $attributes['view_count'] ?? 100,
        created_at: $attributes['created_at'] ?? now()->subHour(),
        thumbnail_url: $attributes['thumbnail_url'] ?? 'http://thumb.jpg',
        duration: $attributes['duration'] ?? 30.0,
        vod_offset: $attributes['vod_offset'] ?? 10,
        is_featured: $attributes['is_featured'] ?? false
    );
}
