<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Horizon\Horizon;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;

class AddHorizonCspNonce
{
    /**
     * @param  Closure(Request): (Response)  $next
     *
     * @throws RandomException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));

        Horizon::cspNonce($nonce);

        $response = $next($request);

        // csp with unsafe-eval is kinda meh so im making it very strict lol
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'none'",
                "script-src 'self' 'nonce-$nonce' 'unsafe-eval'",
                "style-src 'self' 'nonce-$nonce' https://fonts.bunny.net",
                "font-src 'self' https://fonts.bunny.net",
                "connect-src 'self'",
                "img-src 'self' data:",
                "base-uri 'none'",
                "object-src 'none'",
                "frame-ancestors 'none'",
            ]),
        );

        return $response;
    }
}
