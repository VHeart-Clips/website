<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use Illuminate\Http\RedirectResponse;

class UpdatePreferencesController extends Controller
{
    public function __invoke(UpdatePreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->updatePreferences($validated);

        return back();
    }
}
