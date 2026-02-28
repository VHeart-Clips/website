<nav class="sticky top-0 md:top-2 my-2 z-100">
    <header
        class="flex items-center h-14 w-full px-3 sm:px-4    text-gray-900 dark:text-white/85   bg-white/75 dark:bg-black/80    border border-gray-200 dark:border-white/20    ring-black/5 ring-1 dark:ring-0    backdrop-blur-md rounded-2xl    shadow-xl dark:shadow-none"
    >
        <div class="flex min-w-0 flex-1 items-center shrink-0">
            <a
                href="{{ request()->routeIs('static') ? '#' : route('static') }}"
                class="flex items-center hover:opacity-80 h-6 sm:h-8"
                aria-label="Homepage"
            >
                <x-ui.branding.banner class="h-6 sm:h-8"/>
            </a>
        </div>

        <div class="flex gap-1 lg:gap-1.5">
            <x-layout.shared.link href="{{ route('submitclip.create') }}" :active="request()->routeIs('submitclip.create')">
                <x-slot:icon>
                    <x-lucide-send defer />
                </x-slot:icon>

                {{ __('navigation.submit_clips') }}
            </x-layout.shared.link>

            <x-layout.shared.link href="{{ route('vote') }}" :active="request()->routeIs('vote')">
                <x-slot:icon>
                    <x-lucide-thumbs-up defer />
                </x-slot:icon>

                {{ __('navigation.evaluate_clips') }}
            </x-layout.shared.link>

            @auth
                <x-ui.dropdown>
                    <x-ui.dropdown.trigger>
                        <button
                            class="group inline-flex h-auto items-center gap-2 px-1 py-1 sm:px-2 sm:py-1.5 cursor-pointer rounded-xl outline-hidden select-none transition-colors duration-200 ease-in-out text-gray-600 hover:bg-accent/15 hover:text-gray-900 focus-visible:bg-accent/15 focus-visible:text-gray-900 focus-visible:ring-2 focus-visible:ring-accent/50 dark:text-white/70 dark:hover:text-white dark:focus-visible:text-white"
                        >
                            <x-ui.avatar
                                class="size-8 text-base"
                                :src="auth()->user()->proxiedContentUrl()"
                                :name="auth()->user()->name"
                                :force="true"
                            />

                            <span
                                class="hidden text-sm font-medium xl:inline"
                            >
                                {{ auth()->user()->name }}
                            </span>

                            <x-lucide-chevron-down
                                class="hidden size-4 opacity-70 transition-transform duration-200 group-hover:scale-110 lg:block"
                                x-bind:class="{ 'rotate-180': open }"
                                defer
                            />
                        </button>
                    </x-ui.dropdown.trigger>

                    <x-ui.dropdown.content align="right" class="min-w-42">
                        <x-ui.dropdown.item href="{{ route('dashboard') }}">
                            {{ __('navigation.dashboard') }}
                        </x-ui.dropdown.item>

                        <x-ui.dropdown.item href="{{ route('profile.edit') }}">
                            {{ __('navigation.settings') }}
                        </x-ui.dropdown.item>

                        <x-ui.dropdown.separator/>

                        <form method="POST" action="{{ route('logout') }}">
                            <x-ui.dropdown.item as="button" type="submit" variant="destructive">
                                {{ __('navigation.logout') }}
                            </x-ui.dropdown.item>
                        </form>
                    </x-ui.dropdown.content>
                </x-ui.dropdown>
            @endauth

            @guest
                <x-layout.shared.link href="{{ route('login') }}" :active="request()->routeIs('login')">
                    <x-slot:icon>
                        <x-lucide-log-in />
                    </x-slot:icon>

                    {{ __('navigation.login') }}
                </x-layout.shared.link>
            @endguest
        </div>
    </header>
</nav>
