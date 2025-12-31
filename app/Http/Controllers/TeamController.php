<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {        
        $teamMembers = [];

        $roles = Role::query()->orderBy('weight','desc')->orderBy('name','asc')->where('public',true)->get();

        foreach ($roles as $role )
        {

            $teamInfo = [
                'name' => $role->name,
                'members' => []
            ];

            foreach ($role->users as $member) {
                $teamInfo['members'][] = [
                    'name' => $member->name, 
                    'avatar' => $member->avatar_url
                ];
            }

            $teamMembers[] = $teamInfo;
        }

        return Inertia::render('team', [
            'roles' => $teamMembers
        ]);
    }
}
