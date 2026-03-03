<x-layout title="Log-in" class="flex flex-col items-center justify-center max-w-xl m-auto">
    <div class="w-full rounded-xl border border-black/10 bg-white/60 shadow-xl text-card-foreground dark:border-white/20 dark:bg-black/40 dark:shadow-purple-900/30 backdrop-blur-md">
        <div class="flex flex-col space-y-6 text-center p-6">
            <div class="flex justify-center">
                <div class="relative group">
                    <x-ui.branding.logo defer class="h-24 w-24 drop-shadow-xl transition-transform duration-300 hover:scale-105 dark:drop-shadow-2xl" />
                    <div class="absolute inset-0 rounded-full bg-purple-500/20 blur-xl"></div>
                    <div class="absolute -inset-4 animate-pulse rounded-full border-2 border-purple-500/20"></div>
                </div>
            </div>

            <h3 class="text-4xl font-bold tracking-tight bg-linear-to-r from-purple-700 to-cyan-700 bg-clip-text text-transparent dark:from-purple-300 dark:to-cyan-300">
                {{ __('login.title') }}
            </h3>
        </div>

        <div class="p-6 pt-0 space-y-6">
            <p class="text-center text-lg leading-relaxed text-gray-800 dark:text-gray-200">
                {{ __('login.description') }}
            </p>

            <div class="h-0.5 w-32 bg-linear-to-r from-transparent via-gray-400 to-transparent dark:via-gray-600 m-auto"></div>
        </div>

        <div class="flex flex-col items-center p-6 pt-0 space-y-6">
            <div class="group relative w-full">
                <div class="absolute -inset-1 rounded-lg bg-linear-to-r from-purple-600 to-cyan-500 opacity-40 blur-lg transition-opacity duration-300 group-hover:opacity-70"></div>
                <a
                    href="{{ route('auth.redirect') }}"
                    class="relative flex w-full items-center justify-center space-x-3 rounded-lg bg-linear-to-r from-purple-600 to-cyan-500 py-4 text-lg font-bold text-white shadow-lg transition-all duration-300 hover:from-purple-700 hover:to-cyan-600 hover:scale-[1.02] active:scale-95 focus:outline-none focus:ring-4 focus:ring-purple-500/50 disabled:opacity-50 disabled:pointer-events-none"
                >
                    <div class="relative flex items-center justify-center" aria-hidden="true">
                        <x-bi-twitch defer class="size-7" />
                        <div class="absolute inset-0 bg-cyan-400/30 blur-sm"></div>
                    </div>
                    <span>
                        {{ __('login.connect_button') }}
                    </span>
                </a>
            </div>

            <p class="w-full border-t border-black/10 pt-4 text-center text-sm text-gray-600 dark:border-white/20 dark:text-gray-400">
                {{ __('login.terms_notice') }}
            </p>
        </div>
    </div>

    <div class="mt-10 text-center w-full">
        <p class="rounded-xl border border-black/10 bg-white/50 px-6 py-3 text-gray-800 shadow-sm backdrop-blur-sm transition-colors hover:bg-white/70 dark:border-white/20 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/20">
            <span>{{ __('login.community_support') }}</span>
            <span class="ml-2 inline-block animate-pulse text-cyan-600 dark:text-cyan-400">✦</span>
        </p>
    </div>
</x-layout>
