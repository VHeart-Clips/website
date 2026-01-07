<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>
    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }

        .no-script-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
        }

        .no-script-dark {
            background: radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.20) 0%, rgba(10, 10, 26, 0) 45%),
            radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.14) 0%, rgba(10, 10, 26, 0) 50%),
            #0a0a1a;
            color: #ffffff;
        }

        .no-script-light {
            background: radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.12) 0%, rgba(238, 242, 248, 0) 45%),
            radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.10) 0%, rgba(238, 242, 248, 0) 50%),
            #EEF2F8;
            color: #111827;
        }

        .no-script-content {
            width: 100%;
            max-width: 720px;
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
        }

        .no-script-dark .no-script-content {
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(0, 0, 0, 0.35);
        }

        .no-script-light .no-script-content {
            border: 1px solid rgba(17, 24, 39, 0.1);
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        }

        .no-script-logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .no-script-logo {
            height: auto;
            width: 50%;
            max-width: 300px;
        }

        .no-script-dark .no-script-logo {
            filter: drop-shadow(0 0 40px rgba(145, 70, 255, 0.7));
        }

        .no-script-light .no-script-logo {
            filter: drop-shadow(0 0 30px rgba(145, 70, 255, 0.22));
        }

        .no-script-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .no-script-title {
            margin: 0;
            font-size: 22px;
            letter-spacing: -0.01em;
        }

        .no-script-description {
            margin: 10px 0 0;
            line-height: 1.5;
            max-width: 500px;
        }

        .no-script-dark .no-script-description {
            color: rgba(255, 255, 255, 0.85);
        }

        .no-script-light .no-script-description {
            color: rgba(17, 24, 39, 0.85);
        }

        .no-script-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .no-script-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .no-script-dark .no-script-button {
            border: 1px solid rgba(145, 70, 255, 0.35);
            background: linear-gradient(90deg, rgba(145, 70, 255, 0.22), rgba(0, 174, 255, 0.14));
            color: #fff;
        }

        .no-script-light .no-script-button {
            border: 1px solid rgba(145, 70, 255, 0.35);
            background: linear-gradient(90deg, rgba(145, 70, 255, 0.15), rgba(0, 174, 255, 0.10));
            color: #111827;
        }

        .no-script-button:hover {
            transform: scale(1.05);
        }

        .no-script-dark .no-script-button:hover {
            box-shadow: 0 0 20px rgba(145, 70, 255, 0.4);
        }

        .no-script-light .no-script-button:hover {
            box-shadow: 0 0 20px rgba(145, 70, 255, 0.2);
        }

        .no-script-dark { display: none; }
        .no-script-light { display: flex; }

        @media (prefers-color-scheme: dark) {
            .no-script-dark { display: flex; }
            .no-script-light { display: none; }
        }


        html.dark .no-script-dark {
            display: flex !important;
        }

        html.dark .no-script-light {
            display: none !important;
        }

        html:not(.dark) .no-script-dark {
            display: none !important;
        }

        html:not(.dark) .no-script-light {
            display: flex !important;
        }
    </style>
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
    @cookieconsentscripts
</head>
<body class="font-inter antialiased">

<noscript>
    <div class="no-script-container no-script-dark">
        <div class="no-script-content">
            <div class="no-script-logo-container">
                <img src="{{ Vite::asset('resources/images/svg/logo-full-dark.svg') }}" alt="VHeart Logo"
                     class="no-script-logo">
            </div>
            <div class="no-script-inner">
                <div>
                    <h1 class="no-script-title">{{ __('app-blade.title') }}</h1>
                    <p class="no-script-description">
                        {{ __('app-blade.description') }}
                    </p>
                </div>
                <div class="no-script-buttons">
                    <a href="/imprint" class="no-script-button">{{ __('app-blade.imprint') }}</a>
                    <a href="/privacy" class="no-script-button">{{ __('app-blade.privacy') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="no-script-container no-script-light">
        <div class="no-script-content">
            <div class="no-script-logo-container">
                <img src="{{ Vite::asset('resources/images/svg/logo-full-title.svg') }}" alt="VHeart Logo"
                     class="no-script-logo">
            </div>
            <div class="no-script-inner">
                <div>
                    <h1 class="no-script-title">{{ __('app-blade.title') }}</h1>
                    <p class="no-script-description">
                        {{ __('app-blade.description') }}
                    </p>
                </div>
                <div class="no-script-buttons">
                    <a href="/imprint" class="no-script-button">{{ __('app-blade.imprint') }}</a>
                    <a href="/privacy" class="no-script-button">{{ __('app-blade.privacy') }}</a>
                </div>
            </div>
        </div>
    </div>
</noscript>
@inertia
@cookieconsentview
</body>
</html>
