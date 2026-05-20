<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Clip\Compilation;
use App\Models\ShortUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController
{
    public function __invoke(Request $request, ?string $slug = 'fallback'): View|RedirectResponse
    {
        if ($shortUrl = ShortUrl::where('slug', $slug)->first()) {
            $key = 'click:'.$shortUrl->id.':'.hash('sha256', (string) $request->ip());

            if (! cache()->has($key)) {
                $shortUrl->increment('clicks');
                cache()->put($key, true, now()->addHour());
            }

            return redirect()->away($shortUrl->url);
        }

        if ($compilation = Compilation::where('slug', $slug)->whereNotNull('youtube_url')->first()) {
            return redirect()->away($compilation->youtube_url);
        }

        if (preg_match('/^episode-\d+$/i', (string) $slug)) {
            return redirect()->away('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        }

        return redirect()->away(config('app.url'));
    }
}
