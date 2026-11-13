<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Clip;
use App\Models\User;
use App\Models\UserClipIgnore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserClipIgnore>
 */
class UserClipIgnoreFactory extends Factory
{
    protected $model = UserClipIgnore::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clip_id' => Clip::factory(),
            'created_at' => fake()->dateTime(),
        ];
    }
}
