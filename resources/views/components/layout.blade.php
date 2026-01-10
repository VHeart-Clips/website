@props(["title" => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            const stored = '{{ $appearance ?? "system" }}';
            const mql = window.matchMedia('(prefers-color-scheme: dark)');

            function apply(mode) {
                const isDark = mode === 'dark' || (mode === 'system' && mql.matches);
                document.documentElement.classList.toggle('dark', isDark);
            }

            apply(stored);

            if (stored === 'system') {
                const onChange = () => apply('system');
                if (mql.addEventListener) mql.addEventListener('change', onChange);
                else mql.addListener(onChange);
            }

            window.addEventListener('storage', (e) => {
                if (e.key !== 'appearance') return;
                const next = e.newValue || 'system';
                apply(next);
            });
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title>
        @if($title)
            {{ $title }} -
        @endif
        {{ config('app.name', 'Laravel') }}
    </title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @viteReactRefresh
    @vite(['resources/css/app.css'])
    @cookieconsentscripts
</head>
<body class="font-inter antialiased">
{{ $slot }}
@cookieconsentview
</body>
</html>
