<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Reports\ReportReason;
use App\Enums\Reports\ReportStatus;
use App\Enums\Reports\ResolveAction;
use App\Models\Clip;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportable = $this->faker->randomElement([
            User::factory(),
            Clip::factory(),
        ])->create();

        return [
            'user_id' => User::factory(),
            'reportable_id' => $reportable->id,
            'reportable_type' => $reportable->getMorphClass(),
            'reason' => $this->faker->randomElement(ReportReason::cases()),
            'description' => $this->faker->paragraph(),
            'status' => ReportStatus::Pending,
            'resolve_action' => null,
            'claimed_by' => null,
            'claimed_at' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    /**
     * Report is currently being reviewed by an admin.
     */
    public function claimed(?User $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'claimed_by' => $user ?? User::factory(),
            'claimed_at' => now(),
        ]);
    }

    /**
     * Report has been resolved/closed.
     */
    public function resolved(?User $user = null, ?ResolveAction $resolveAction = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReportStatus::Resolved,
            'resolve_action' => $resolveAction ?? $this->faker->randomElement(ResolveAction::cases()),
            'resolved_by' => $user ?? User::factory(),
            'resolved_at' => now(),
            'deleted_at' => now(),
        ]);
    }
}
