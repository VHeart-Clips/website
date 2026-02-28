@php
    static $socials = [
        ['label' => 'Github', 'href' => 'https://github.com/VHeart-Clips', 'light' => '#181717', 'dark' => '#FFFFFF', 'icon' => 'bi-github'],
        ['label' => 'Discord', 'href' => 'https://discord.gg/vheart', 'light' => '#5865F2', 'dark' => '#5865F2', 'icon' => 'bi-discord'],
        ['label' => 'Youtube', 'href' => 'https://www.youtube.com/@vheartclips', 'light' => '#FF0000', 'dark' => '#FF0000', 'icon' => 'bi-youtube'],
        ['label' => 'Twitch', 'href' => 'https://www.twitch.tv/vheartclips', 'light' => '#9146FF', 'dark' => '#9146FF', 'icon' => 'bi-twitch'],
        ['label' => 'Twitter / X', 'href' => 'https://x.com/VHeartClips', 'light' => '#000000', 'dark' => '#FFFFFF', 'icon' => 'bi-twitter-x'],
        ['label' => 'Reddit', 'href' => 'https://www.reddit.com/r/VHeartClips/', 'light' => '#FF4500', 'dark' => '#FF4500', 'icon' => 'bi-reddit'],
        ['label' => 'Bluesky', 'href' => 'https://bsky.app/profile/vheart.net', 'light' => '#1185FE', 'dark' => '#1185FE', 'icon' => 'bi-bluesky'],
    ];

    static $navs = [
        'privacy.footer' => '/privacy',
        'imprint.footer' => '/imprint',
        'faq' => '/faq',
        'team' => '/team',
        'about' => '/about-us'
    ];
@endphp

<div class="sticky bottom-0 md:bottom-2 my-2 z-100">
    <footer class="w-full    text-gray-900 dark:text-white/85   bg-white/75 dark:bg-black/80    border border-gray-200 dark:border-white/20    ring-black/5 ring-1 dark:ring-0    backdrop-blur-md rounded-2xl    shadow-xl dark:shadow-none    transition-all">
        <details class="group xl:hidden w-full">
            <summary class=" grid grid-cols-[auto_auto_1fr] gap-1 md:gap-3 xl:gap-6 items-center h-14 px-1 md:px-2 xl:px-4 rounded-lg cursor-pointer outline-none hover:bg-gray-50 dark:hover:bg-white/5 focus-visible:ring-2 focus-visible:ring-blue-500/50 transition-colors [&::-webkit-details-marker]:hidden">
                <span class="text-sm text-gray-600 dark:text-white/70 items-center group-open:p-2">
                    <span class="whitespace-nowrap items-center flex gap-1 text-foreground">
                        <x-lucide-copyright defer class="size-4 inline" />
                        2026
                        <span class="hidden group-open:inline sm:inline">VHeart</span>
                    </span>
                    <span class="hidden group-open:inline text-muted-foreground">{{ __('footer.all_rights_reserved') }}</span>
                </span>

                <div class="flex items-center gap-1 sm:gap-4 text-sm group-open:hidden">
                    <x-layout.shared.link href="/privacy">
                        {{ __('footer.privacy.footer') }}
                    </x-layout.shared.link>

                    <x-layout.shared.link href="/imprint">
                        {{ __('footer.imprint.footer') }}
                    </x-layout.shared.link>
                </div>
                <div class="hidden group-open:block"></div>

                <x-lucide-chevron-up defer class="size-4 md:size-5 text-gray-500 dark:text-white/70 transition-transform duration-300 group-open:rotate-180 justify-self-end" />
            </summary>

            <div class="flex flex-col items-center gap-6 border-t border-gray-200 dark:border-white/5 px-6 py-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <nav class="flex flex-wrap justify-center gap-x-6 gap-y-2">
                    @foreach($navs as $key => $path)
                        <x-layout.shared.link :href="$path">
                            {{ __('footer.' . $key) }}
                        </x-layout.shared.link>
                    @endforeach
                </nav>

                <div class="flex flex-wrap justify-center gap-4">
                    @foreach($socials as $s)
                        <x-layout.footer.social-icon
                            :label="$s['label']"
                            :href="$s['href']"
                            :light-color="$s['light']"
                            :dark-color="$s['dark']"
                            :icon="$s['icon']"
                        />
                    @endforeach
                </div>

                <div class="flex items-center gap-2 rounded-xl bg-black/5 p-1.5 dark:bg-white/5">
                    <x-layout.footer.cookie />
                    <div class="h-4 w-px bg-gray-300 dark:bg-white/10"></div>
                    <x-ui.language.selector />
                    <div class="h-4 w-px bg-gray-300 dark:bg-white/10"></div>
                    <x-ui.appearance.slider />
                </div>
            </div>
        </details>

        <div class="hidden xl:grid w-full grid-cols-[auto_auto_auto] 2xl:grid-cols-[1fr_auto_1fr] items-center gap-4 px-4 h-14">
            <div class="truncate text-sm font-medium text-gray-600 dark:text-white/70 items-center flex gap-1">
                <x-lucide-copyright defer class="size-4 inline" />
                <span>
                    <span class="text-foreground">2026 VHeart</span>
                    <span class="hidden text-muted-foreground 2xl:inline">{{ __('footer.all_rights_reserved') }}</span>
                </span>
            </div>

            <nav class="flex flex-wrap justify-center gap-x-1 2xl:gap-x-4 gap-y-2">
                @foreach($navs as $key => $path)
                    <x-layout.shared.link :href="$path">
                        {{ __('footer.' . $key) }}
                    </x-layout.shared.link>
                @endforeach
            </nav>

            <div class="flex items-center justify-end gap-3">
                <div class="flex items-center gap-0.5">
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

                <div class="h-4 w-px bg-gray-200 dark:bg-white/10"></div>

                <div class="flex items-center gap-1">
                    <x-layout.footer.cookie />
                    <x-ui.language.selector />
                    <x-ui.appearance.slider />
                </div>
            </div>
        </div>

    </footer>
</div>
