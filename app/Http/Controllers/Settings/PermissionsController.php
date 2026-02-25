<?php

declare(strict_types=1);

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

        $request->user()->update([
            'clip_permission' => $request->boolean('clip_permission'),
        ]);

        return back();
    }
}
