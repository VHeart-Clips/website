<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'desc' => $this->faker->words(),
            'weight' => 0,
            'public' => true,
            'created_at' => fake()->dateTime(),
            'updated_at' => Carbon::now(),
        ];
    }
}
