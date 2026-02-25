<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

/**
 * Logout duh
 */
class DestroyAuthenticatedSessionController extends Controller implements HasMiddleware
{
    public function __invoke(Request $request): RedirectResponse
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return to_route('home');
    }

    public static function middleware(): array
    {
        return ['auth:web'];
    }
}
