<?php

declare(strict_types=1);

namespace App\Policies\Contracts;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

interface BannablePolicy
{
    /**
     * can $user ban the model
     */
    public function ban(User $user, Model $model): Response|bool;

    /**
     * can $user unban the model
     */
    public function unban(User $user, Model $model): Response|bool;
}
