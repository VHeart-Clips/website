<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Email;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;

class EmailVerificationPromptController extends Controller implements HasMiddleware
{
    public function __invoke(Request $request)
    {
        if ($request->user()->email === null || $request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return Inertia::render('auth/verify-email');
    }

    public static function middleware(): array
    {
        return ['auth:web'];
    }
}
