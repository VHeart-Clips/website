<?php

declare(strict_types=1);

namespace Database\Factories\Clip;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip\Compilation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Compilation>
 */
class CompilationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $slug = Str::uuid()->toString();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => $slug,
            'type' => CompilationType::Manual,
            'status' => CompilationStatus::Planned,
            'description' => fake()->paragraph(),
            'youtube_url' => fake()->url(),
            'auto_fill_seconds' => null,
        ];
    }

    public function withoutUser(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
            ];
        });
    }
}
