<?php

declare(strict_types=1);

use App\Enums\Broadcaster\BroadcasterConsent;
use App\Enums\Eloquent\SetOperator;
use App\Models\Broadcaster\Broadcaster;
use App\Models\User;
use Pest\Expectation;

describe('whereHasGivenConsent scope', function () {
    beforeEach(function () {
        Broadcaster::factory()
            ->recycle(User::factory()->create(['id' => 1]))
            ->create(['id' => 1, 'consent' => [BroadcasterConsent::Compilations]]);

        Broadcaster::factory()
            ->recycle(User::factory()->create(['id' => 2]))
            ->create(['id' => 2, 'consent' => [BroadcasterConsent::Shorts]]);

        Broadcaster::factory()
            ->recycle(User::factory()->create(['id' => 3]))
            ->create(['id' => 3, 'consent' => [BroadcasterConsent::Compilations, BroadcasterConsent::Shorts]]);

        Broadcaster::factory()
            ->recycle(User::factory()->create(['id' => 4]))
            ->create(['id' => 4, 'consent' => []]);
    });

    it('should find any broadcaster with any consent if none was specified', function () {
        $result = Broadcaster::query()->whereHasGivenConsent()->get();

        expect($result)->toHaveCount(3)
            ->each(fn (Expectation $item) => $item->consent->not->toBeEmpty());
    });

    it('should not find any broadcaster if an empty input was specified', function (mixed $input) {
        $result = Broadcaster::query()->whereHasGivenConsent($input)->get();

        expect($result)->toHaveCount(1)
            ->first()->id->toBe(4);
    })->with([
        'empty array' => [[]],
        'empty collection' => [fn () => collect()],
    ]);

    it('allows the input to be a single enum object', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(BroadcasterConsent::Compilations, SetOperator::Any)
            ->get();

        expect($result)->toHaveCount(2)
            ->each(fn (Expectation $item) => $item->consent->toContain(BroadcasterConsent::Compilations));
    });

    it('allows the input to be a collection of enum objects', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(
                collect([BroadcasterConsent::Compilations, BroadcasterConsent::Shorts]),
                SetOperator::Any
            )->get();

        expect($result)->toHaveCount(3)
            ->each(fn (Expectation $item) => $item->consent->not->toBeEmpty());
    });

    it('finds broadcasters with at least one matching consent', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(BroadcasterConsent::Compilations, SetOperator::Any)
            ->get();

        expect($result)->toHaveCount(2)
            ->each(fn (Expectation $item) => $item->consent->toContain(BroadcasterConsent::Compilations));
    });

    it('finds broadcasters with some but not all consents', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(
                [BroadcasterConsent::Compilations, BroadcasterConsent::Shorts],
                SetOperator::AnyMissing
            )->get();


        expect($result)->toHaveCount(3)
            ->pluck('id')->all()->toEqual([1, 2, 4]);
    });

    it('finds broadcasters with all matching consents', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(
                [BroadcasterConsent::Compilations, BroadcasterConsent::Shorts],
                SetOperator::All
            )->get();

        expect($result)->toHaveCount(1)
            ->first()->consent->toContainEqual(BroadcasterConsent::Compilations, BroadcasterConsent::Shorts);
    });

    it('finds broadcasters with no matching consents', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(
                [BroadcasterConsent::Compilations, BroadcasterConsent::Shorts],
                SetOperator::None
            )->get();

        expect($result)->toHaveCount(1)
            ->first()->id->toBe(4);
    });

    it('finds broadcasters with exactly matching consents', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent(
                [BroadcasterConsent::Compilations, BroadcasterConsent::Shorts],
                SetOperator::Exact
            )->get();

        expect($result)->toHaveCount(1)
            ->first()->consent->toContainEqual(BroadcasterConsent::Compilations, BroadcasterConsent::Shorts);
    });

    it('uses SetOperator::Exact by default', function () {
        $result = Broadcaster::query()
            ->whereHasGivenConsent([BroadcasterConsent::Compilations, BroadcasterConsent::Shorts])
            ->get();

        expect($result)->toHaveCount(1)
            ->first()->consent->toContainEqual(BroadcasterConsent::Compilations, BroadcasterConsent::Shorts);
    });
});
