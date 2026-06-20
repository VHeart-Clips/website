@php
    static $socials = [
        ['label' => 'Github', 'href' => 'https://github.com/VHeart-Clips', 'light' => '#181717', 'dark' => '#FFFFFF', 'icon' => 'simpleicon-github'],
        ['label' => 'Discord', 'href' => 'https://go.vheart.net/discord', 'light' => '#5865F2', 'dark' => '#5865F2', 'icon' => 'simpleicon-discord'],
        ['label' => 'Youtube', 'href' => 'https://www.youtube.com/@vheartclips', 'light' => '#FF0000', 'dark' => '#FF0000', 'icon' => 'simpleicon-youtube'],
        ['label' => 'Twitch', 'href' => 'https://www.twitch.tv/vheartclips', 'light' => '#9146FF', 'dark' => '#9146FF', 'icon' => 'simpleicon-twitch'],
        ['label' => 'Twitter / X', 'href' => 'https://x.com/VHeartClips', 'light' => '#000000', 'dark' => '#FFFFFF', 'icon' => 'simpleicon-x'],
        ['label' => 'Reddit', 'href' => 'https://www.reddit.com/r/VHeartClips/', 'light' => '#FF4500', 'dark' => '#FF4500', 'icon' => 'simpleicon-reddit'],
        ['label' => 'Bluesky', 'href' => 'https://bsky.app/profile/vheart.net', 'light' => '#1185FE', 'dark' => '#1185FE', 'icon' => 'simpleicon-bluesky'],
    ];
    static $navs = [
        'privacy' => 'privacy.footer',
        'imprint' => 'imprint.footer',
        'terms' => 'terms.footer',
    ];
@endphp

<div
    class="mx-auto flex w-full max-w-(--breakpoint-2xl) flex-col items-center gap-2 px-8 py-2 text-center text-base text-gray-400 dark:text-gray-500 lg:flex-row lg:flex-wrap lg:justify-between lg:gap-3 lg:text-left">
    <div class="flex flex-wrap items-center justify-center gap-1">
        <x-lucide-copyright class="size-3.5"/>
        <span>{{ now()->year }} VHeart</span>
        <span>{{ __('footer.all_rights_reserved') }}</span>
    </div>

    <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
        @foreach ($navs as $route => $key)
            <a href="{{ route($route) }}" class="hover:text-gray-600 dark:hover:text-gray-300">
                {{ __('footer.' . $key) }}
            </a>
        @endforeach
    </nav>

    <div class="flex flex-wrap items-center justify-center gap-1">
        @foreach($socials as $s)
            <x-layout.footer.social-icon
                :label="$s['label']"
                :href="$s['href']"
                :light-color="$s['light']"
                :dark-color="$s['dark']"
                :icon="$s['icon']"
                icon-size="size-5"
            />
        @endforeach
    </div>
</div>
