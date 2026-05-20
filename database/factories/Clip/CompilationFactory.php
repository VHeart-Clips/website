<?php

declare(strict_types=1);

namespace Database\Factories\Clip;

use App\Enums\Clips\CompilationStatus;
use App\Enums\Clips\CompilationType;
use App\Models\Clip\Compilation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Compilation>
 */
#[UseModel(Compilation::class)]
class CompilationFactory extends Factory
{
    /**
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
            'type' => CompilationType::LongVideo,
            'status' => CompilationStatus::Planned,
            'description' => fake()->paragraph(),
            'youtube_url' => fake()->url(),
        ];
    }

    public function withoutUser(): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
        ]);
    }
}
