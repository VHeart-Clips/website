<?php

declare(strict_types=1);

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IpDebugController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 404);

        return response()->json([
            'resolved' => [
                'ip' => request()->ip(),
                'ips' => request()->ips(),
            ],
            'headers' => [
                'x-forwarded-for' => request()->header('X-Forwarded-For'),
                'x-real-ip' => request()->header('X-Real-IP'),
                'x-forwarded-proto' => request()->header('X-Forwarded-Proto'),
                'x-forwarded-host' => request()->header('X-Forwarded-Host'),
                'cf-connecting-ip' => request()->header('CF-Connecting-IP'),
                'cf-ipcountry' => request()->header('CF-IPCountry'),
            ],
            'server' => [
                'remote_addr' => [
                    'laravel' => request()->server('REMOTE_ADDR'),
                    'php' => $_SERVER['REMOTE_ADDR'] ?? null,
                ],
                'server_addr' => [
                    'laravel' => request()->server('SERVER_ADDR'),
                    'php' => $_SERVER['SERVER_ADDR'] ?? null,
                ],
            ],
            'proxy' => [
                'trusted_proxies' => Request::getTrustedProxies(),
                'trusted_headers' => Request::getTrustedHeaderSet(),
                'is_secure' => request()->isSecure(),
                'is_from_trusted_proxy' => request()->isFromTrustedProxy(),
            ],
            'request' => [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
