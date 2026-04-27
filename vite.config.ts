import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { sentryVitePlugin } from '@sentry/vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    define: {
        __SENTRY_RELEASE__: JSON.stringify(
            process.env.VITE_SENTRY_RELEASE ?? 'dev',
        ),
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',
                'resources/js/alpine.ts',
                'resources/js/sentry.ts',
                'resources/css/filament/admin.css',
                'resources/css/filament/dashboard.css',
            ],
            assets: [
                'resources/images/**',
                'resources/fonts/**/*.(woff2|woff|ttf)',
            ],
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        sentryVitePlugin({
            org: 'vheart',
            project: 'website-frontend',
            url: 'https://glitchtip.vheart.net/',
            release: {
                name: process.env.VITE_SENTRY_RELEASE ?? 'dev',
            },
            bundleSizeOptimizations: {
                excludeDebugStatements: true,
                excludeReplayIframe: true,
                excludeReplayShadowDom: true,
                excludeReplayWorker: true,
            },
            sourcemaps: {
                filesToDeleteAfterUpload: [
                    './**/*.map',
                    '.*/**/public/**/*.map',
                    './dist/**/client/**/*.map',
                ],
            },
        }),
    ],
    build: {
        target: 'baseline-widely-available',
        sourcemap: true,
    },
});
