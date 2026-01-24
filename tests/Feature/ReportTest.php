<?php

declare(strict_types=1);

use App\Models\Report;
use App\Models\User;

describe('scopes', function () {
    test('unclaimed scope only returns reports with no claimer', function () {
        Report::factory()->count(3)->create();
        Report::factory()->claimed()->count(2)->create();

        expect(Report::unclaimed()->count())->toBe(3);
    });

    test('claimed scope only returns reports that are claimed', function () {
        Report::factory()->count(2)->create();
        Report::factory()->claimed()->count(3)->create();

        expect(Report::claimed()->count())->toBe(3);
    });

    test('claimedBy scope filters by specific user', function () {
        $adminA = User::factory()->create();
        $adminB = User::factory()->create();

        Report::factory()->claimed($adminA)->count(2)->create();
        Report::factory()->claimed($adminB)->count(1)->create();

        expect(Report::claimedBy($adminA)->count())->toBe(2)
            ->and(Report::claimedBy($adminB)->count())->toBe(1);
    });

    test('claimedByMe scope filters by authenticated user', function () {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me);

        Report::factory()->claimed($me)->count(2)->create();
        Report::factory()->claimed($other)->count(1)->create();
        Report::factory()->create();

        expect(Report::claimedByMe()->count())->toBe(2);
    });
});
