<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Role\RoleUserListResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return Inertia::render('team', [
            'total_members' => Inertia::once(static fn () => User::query()
                ->whereHas('roles', fn (Builder $builder) => $builder->where('public', true))
                ->count()),
            'roles' => Inertia::once(static fn () => Role::query()
                ->where('public', true)
                ->orderBy('weight', 'desc')
                ->orderBy('name')
                ->with('users', function (BelongsToMany $query): void {
                    $query->select(['id', 'name', 'avatar_url'])->orderBy('name');
                })
                ->get()
                ->toResourceCollection(RoleUserListResource::class)),
        ]);
    }
}
