<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __invoke(Request $request): View
    {
        $total = User::query()
            ->whereHas('roles', fn (Builder $builder) => $builder->where('public', true))
            ->count();

        $roles = Role::query()
            ->where('public', true)
            ->orderBy('weight', 'desc')
            ->orderBy('id')
            ->with(['users' => fn ($builder) => $builder
                ->orderBy('id')] // i think this is the fairest way to sort it
            )
            ->get();

        return view('team.index', ['total' => $total, 'roles' => $roles]);
    }
}
