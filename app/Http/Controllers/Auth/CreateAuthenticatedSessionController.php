<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Shows the Login Prompt
 */
class CreateAuthenticatedSessionController extends Controller implements HasMiddleware
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public static function middleware(): array
    {
        return ['guest'];
    }
}
