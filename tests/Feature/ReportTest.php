<?php

declare(strict_types=1);

use App\Jobs\Discord\Reports\DeleteReportWebhookJob;
use App\Jobs\Discord\Reports\ReportWebhookJob;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

beforeEach(fn () => Bus::fake([
    ReportWebhookJob::class,
    DeleteReportWebhookJob::class,
]));

describe('ReportWebhookJob', function () {
    it('dispatches after report being created', function () {
        Report::factory()->create();

        Bus::assertDispatched(ReportWebhookJob::class);
    });

    it('dispatches after report being updated if we have a discord message id attached', function () {
        $report = Report::factory()->createQuietly(['discord_message_id' => 1]);
        $report->refresh();

        $report->update([
            'description' => Str::random(),
        ]);

        Bus::assertDispatched(ReportWebhookJob::class);
    });

    it('does not dispatch after report being updated if we dont have a discord message id attached', function () {
        $report = Report::factory()->createQuietly(['discord_message_id' => null]);
        $report->refresh();

        $report->update([
            'description' => Str::random(),
        ]);

        Bus::assertNotDispatched(ReportWebhookJob::class);
    });
});

describe('DeleteReportWebhookJob', function () {
    it('dispatches after report being force deleted with discord message id attached', function () {
        $report = Report::factory()->createQuietly(['discord_message_id' => 1]);
        $report->forceDelete();

        Bus::assertDispatched(DeleteReportWebhookJob::class);
    });

    it('does not dispatch after report being deleted with a discord message id attached', function () {
        $report = Report::factory()->createQuietly(['discord_message_id' => 1]);
        $report->delete();

        Bus::assertNotDispatched(DeleteReportWebhookJob::class);
    });

    it('does not dispatch after report being force deleted without discord message id attached', function () {
        $report = Report::factory()->createQuietly(['discord_message_id' => null]);
        $report->forceDelete();

        Bus::assertNotDispatched(DeleteReportWebhookJob::class);
    });
});

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
