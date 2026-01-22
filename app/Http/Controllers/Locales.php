<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LocalesRequest;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Http\JsonResponse;
use Illuminate\Translation\Translator;

class Locales
{
    protected Loader $loader;

    public function __construct(Translator $translator)
    {
        $this->loader = $translator->getLoader();
    }

    /**
     * Returns translation data given a specific local and namespace.
     */
    public function __invoke(LocalesRequest $request): JsonResponse
    {
        $locales = $request->locales();
        $namespaces = $request->namespaces();

        $response = [];
        foreach ($locales as $locale) {
            $response[$locale] = [];
            foreach ($namespaces as $namespace) {
                $response[$locale][$namespace] = $this->i18n(
                    $this->loader->load($locale, str_replace('.', '/', $namespace))
                );
            }
        }

        return new JsonResponse($response, 200, [
            // Cache this in the browser for an hour, and allow the browser to use a stale
            // cache for up to a day after it was created while it fetches an updated set
            // of translation keys.
            'Cache-Control' => 'public, max-age=3600, must-revalidate, stale-while-revalidate=86400',
            // ETag is set automatically by Middleware\ETag
        ]);
    }

    /**
     * Convert standard Laravel translation keys that look like ":foo"
     * into key structures that are supported by the front-end i18n
     * library, like "{{foo}}".
     */
    protected function i18n(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->i18n($value);
            } else {
                // Find a Laravel style translation replacement in the string and replace it with
                // one that the front-end is able to use. This won't always be present, especially
                // for complex strings or things where we'd never have a backend component anyway.
                // We strictly require the key to start with a letter/underscore [a-zA-Z_]
                //
                // For example:
                // "Hello :name, the :notifications.0.title notification needs :count actions :foo.0.bar before 12:00."
                //
                // Becomes:
                // "Hello {{name}}, the {{notifications.0.title}} notification needs {{count}} actions {{foo.0.bar}} before 12:00."

                // https://regex101.com/r/dA9Xs6/1
                $data[$key] = preg_replace('/:([a-zA-Z_](?:[\w.-]*\w)?)([^\w:]?|$)/m', '{{$1}}$2', $value);
            }
        }

        return $data;
    }
}
