<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/permissions');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'clip_permission' => ['required', 'boolean'],
        ]);

        $clipPermission = filter_var(
            $request->input('clip_permission'),
            FILTER_VALIDATE_BOOLEAN,
        );

        $request->user()?->forceFill([
            'clip_permission' => $clipPermission,
        ])->save();

        return back();
    }
}
