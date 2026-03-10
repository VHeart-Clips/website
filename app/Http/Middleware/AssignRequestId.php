<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

use function Sentry\configureScope;

class AssignRequestId
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Header set by internal nginx proxy or cloudflare with fallback for local env
        $requestId = $request->headers->get('X-Request-ID', Str::uuid()->toString());

        // just in case we just generated it
        $request->headers->set('X-Request-ID', $requestId);

        Context::addIf('request_id', $requestId);

        configureScope(function (Scope $scope) use ($requestId): void {
            $scope->setTag('request_id', $requestId);
        });

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
