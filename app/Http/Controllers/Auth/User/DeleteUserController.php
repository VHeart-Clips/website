<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DeleteUserRequest;
use App\Jobs\RemoverUserDataJob;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class DeleteUserController extends Controller
{
    public function __invoke(DeleteUserRequest $request, AppAuthentication $mfa): Redirector|RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        dispatch(new RemoverUserDataJob($user->id));

        return redirect('/');
    }
}
