<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ExternalContentProxyType;
use App\Http\Requests\ContentProxyRequest;
use Illuminate\Support\Facades\Http;

/**
 * We act as a proxy to hide the user ip from twitch/youtube
 * We use Cloudflare to prevent additional load on our end via caching
 */
class ExternalContentProxyController extends Controller
{
    public const int STREAM_BUFFER_SIZE = 8192;

    public function __invoke(ContentProxyRequest $request, ExternalContentProxyType $type, string $identifier)
    {
        $resource = $type->getResource($identifier);

        $response = Http::withOptions(['stream' => true])
            ->timeout(5)
            ->get($resource);

        if ($response->failed()) {
            abort(404);
        }

        $contentType = $response->header('Content-Type');
        if ($contentType !== 'application/json' && ! str_starts_with($contentType, 'image/')) {
            abort(415);
        }

        $headers = [
            'Content-Type' => $contentType,
            'ETag' => $response->header('ETag'),
            'Cache-Control' => 'public, max-age=31536000, s-maxage=31536000, immutable',
        ];

        return response()->stream(function () use ($response): void {
            $body = $response->toPsrResponse()->getBody();

            while (! $body->eof()) {
                echo $body->read(self::STREAM_BUFFER_SIZE);
            }
        }, 200, array_filter($headers));
    }
}
