@use(Illuminate\Support\Facades\Vite)
@props([
    "title" => null,
    "description" => null,
    'ogImage' => null,
    'background' => true,
    "robots" => "index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1",
    "discordEmbedUrl" => route('embed.discord.default')
])
@php
    $title ??= config('app.name', 'Laravel') . ' - Streamer Clip Compilations für den Tierschutz.';
    $description ??= 'VHeart ist ein Zusammenschluss von Cuttern, Streamern und Künstlern, die eine hochwertige Clip-Compilation für den guten Zweck entwickelt haben!';
    $ogImage ??= Vite::asset('resources/images/png/og-banner.png');

    // make sure to keep this in sync with the og banner size, ratio should always be 1.91:1 though
    $ogImageWidth = 1200;
    $ogImageHeight = 630;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Basic SEO / UX stuff --}}
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0a0a" media="(prefers-color-scheme: dark)">
    <meta name="format-detection" content="telephone=no">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="discord:component-embed" type="application/json" href="{{ $discordEmbedUrl }}">
    <link rel="canonical" href="{{ request()->has('cursor') ? url()->full() : url()->current() }}">
    <link rel="author" href="{{ route('team') }}">

    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@type": "WebSite",
            "name": "{{ config('app.name') }}",
            "url": "{{ route('home') }}"
        }
    </script>
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@type": "Organization",
            "name": "{{ config('app.name') }}",
            "url": "{{ route('home') }}",
            "logo": "{{ Vite::asset('resources/images/svg/logo-light.svg') }}",
            "sameAs": [
                "https://github.com/VHeart-Clips",
                "https://www.youtube.com/@vheartclips",
                "https://www.twitch.tv/vheartclips",
                "https://x.com/VHeartClips",
                "https://www.reddit.com/r/VHeartClips/",
                "https://bsky.app/profile/vheart.net"
            ]
        }
    </script>

    {{-- OpenGraph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="{{ $ogImageWidth }}">
    <meta property="og:image:height" content="{{ $ogImageHeight }}">
    <meta property="og:image:alt" content="{{ $title }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'de' ? 'de_DE' : 'en_US' }}">
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@VHeartClips">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $title }}">

    {{ $meta ?? '' }}

    {{-- Performance related stuff, i would like to use preconnect but that would bypass our cookie stuff, dns prefetch should be fine though --}}
    <link rel="dns-prefetch" href="https://clips.twitch.tv">
    <link rel="dns-prefetch" href="https://clips-media-assets2.twitch.tv">
    <link rel="dns-prefetch" href="https://production.assets.clips.twitchcdn.net">
    <link rel="dns-prefetch" href="https://static-cdn.jtvnw.net">

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

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="{{ Vite::asset('resources/images/svg/logo-light.svg') }}" type="image/svg+xml">
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
