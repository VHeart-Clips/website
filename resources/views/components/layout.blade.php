@props(["title" => null, 'background' => true])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        const mediaQuery = window?.matchMedia('(prefers-color-scheme: dark)');

        function applyAppearance() {
            const cookieMatch = document.cookie.match(/(?:^|; )appearance=([^;]*)/);
            const appearance = localStorage.getItem('theme') || (cookieMatch ? cookieMatch[1] : null) || '{{ $appearance ?? "system" }}';
            const isDark = appearance === 'dark' || (appearance === 'system' && mediaQuery?.matches);

            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

            window.dispatchEvent(new CustomEvent('appearanceChanged', {
                detail: { appearance, isDark }
            }));
        }

        (function() {
            applyAppearance();

            mediaQuery?.addEventListener('change', applyAppearance);
            window?.addEventListener('storage', applyAppearance);
            window?.cookieStore?.addEventListener('change', applyAppearance);
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }

        @if($background)
            body {
                background:
                    radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.15) 0%, rgba(255, 255, 255, 0) 45%) fixed,
                    radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.15) 0%, rgba(255, 255, 255, 0) 50%) #ffffff fixed;
            }

            html.dark body {
                background:
                    radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.20) 0%, rgba(10, 10, 26, 0) 45%) fixed,
                    radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.14) 0%, rgba(10, 10, 26, 0) 50%) #0a0a1a fixed;
            }
        @endif
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
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @cookieconsentscripts
</head>
<body class="font-inter antialiased">
    <div class="flex flex-col m-auto min-h-svh w-[95svw] md:w-[98svw] max-w-480">
        <x-layout.header />

        <main {{ $attributes->twMerge("grow") }}>
            <x-layout.shared.onboarding-alert />
            {{ $slot }}
        </main>

        <x-layout.footer />
    </div>

    {{-- use `@pushonce('elements', 'unique identifier') ... @endpushonce` to insert elements we may need only once per page (e.g. modals) --}}
    {{-- otherwise, loops on them will explode the page in size lol --}}
    {{-- @see https://laravel.com/docs/12.x/blade#the-once-directive --}}
    @stack('elements')

    {{-- BladeUI puts deferred SVG icons in this placeholder which gets used via id "pointers" --}}
    {{-- especially useful for icons that get used a ton like the clock or similar --}}
    {{-- this reduces the size of the page and the time to parse the DOM as many SVGs can create very deep structures --}}
    {{-- @see https://github.com/driesvints/blade-icons?tab=readme-ov-file#deferring-icons --}}
    <svg hidden class="hidden">
        @stack('bladeicons')
    </svg>

    @cookieconsentview
</body>
</html>
