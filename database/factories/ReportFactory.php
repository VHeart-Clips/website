<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Reports\ReportReason;
use App\Enums\Reports\ReportStatus;
use App\Models\Clip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
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
        return $this->state(fn (array $attributes) => [
            'claimed_by' => $user ?? User::factory(),
            'claimed_at' => now(),
        ]);
    }

    /**
     * Report has been resolved/closed.
     */
    public function resolved(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Resolved,
            'resolved_by' => $user ?? User::factory(),
            'resolved_at' => now(),
        ]);
    }
}
