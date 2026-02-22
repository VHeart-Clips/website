<?php

declare(strict_types=1);

use App\Http\Middleware\BroadcasterDashboard;
use App\Http\Resources\PrivateClipResource;
use App\Models\Clip;
use App\Models\Scopes\ClipPermissionScope;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function (Request $request) {
        return Redirect::route('dashboard.main', $request->user()->id);
    })->name('dashboard');

    Route::middleware(BroadcasterDashboard::class)
        ->missing(function () {
            return Redirect::route('home');
        })->group(function () {
            Route::get('/dashboard/{user}', function (User $user, Request $request) {
                return Inertia::render('dashboard/main');
            })->name('dashboard.main');

            Route::get('/dashboard/{user}/clips', function (User $user, Request $request) {

                return Inertia::render('dashboard/clips', [
                    'clips' => Inertia::scroll(static function () use ($user) {
                        $clip = Clip::query()
                            ->withCount(['votes' => fn ($q) => $q->where('voted', true)->where('type', App\Enums\ClipVoteType::Public)])
                            ->orderByDesc('id')
                            ->where('broadcaster_id', $user->id)
                            ->withoutGlobalScope(ClipPermissionScope::class)
                            ->with(['category', 'creator', 'tags'])
                            ->cursorPaginate();

                        return $clip->toResourceCollection(PrivateClipResource::class);
                    }),
                ]);
            })->name('dashboard.clips');

            Route::get('/dashboard/{user}/permissions', function (User $user, Request $request) {
                return Inertia::render('dashboard/permissions');
            })->name('dashboard.permissions');
        });
});
