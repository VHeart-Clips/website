<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __invoke(Request $request): View
    {
        $roles = Role::query()
            ->where('public', true)
            ->orderBy('weight', 'desc')
            ->orderBy('id')
            ->with([
                // i think this is the fairest way to sort it
                'users' => fn (Builder $builder): Builder => $builder->orderBy('id')]
            )
            ->get();

        return view('team.index', ['roles' => $roles]);
    }
}
