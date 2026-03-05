<?php

declare(strict_types=1);

namespace Database\Factories\Broadcaster;

use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterSubmissionFilter;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<BroadcasterSubmissionFilter>
 */
class BroadcasterSubmissionFilterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filterableClass = $this->faker->randomElement([
            User::class,
            Category::class,
        ]);

        return [
            'broadcaster_id' => Broadcaster::factory(),
            'filterable_id' => $filterableClass::factory(),
            'filterable_type' => (new $filterableClass)->getMorphClass(),
            'state' => $this->faker->randomElement([true, false]),
        ];
    }

    public function allowed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => true,
        ]);
    }

    public function denied(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => false,
        ]);
    }

    public function filterUser(User|int|null $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'filterable_id' => ($user instanceof User ? $user->id : $user) ?? User::factory(),
            'filterable_type' => (new User)->getMorphClass(),
        ]);
    }

    public function filterCategory(Category|int|null $category = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'filterable_id' => ($category instanceof Category ? $category->id : $category) ?? Category::factory(),
            'filterable_type' => (new Category)->getMorphClass(),
        ]);
    }

    /**
     * Allows you to set any model, make sure its implementing the HasFactory trait lol
     *
     * @return $this
     */
    public function filterModel(Model $model): static
    {
        return $this->state(fn (array $attributes): array => [
            'filterable_id' => $model->getKey() ?? $model::factory(),
            'filterable_type' => $model->getMorphClass(),
        ]);
    }
}
