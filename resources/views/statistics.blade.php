<x-layout class="max-w-5xl w-full mx-auto space-y-6" :title="__('user.statistics.title')">
    <div class="m-auto py-8">
        <x-ui.card variant="glass">
            <x-ui.card.header class="pb-6 border-b border-border">
                <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                    <h1>{{ __('user.statistics.heading') }}</h1>
                </x-ui.card.title>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-foreground">
                        {{ __('user.statistics.subheading') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ __('user.statistics.description') }}
                    </p>
                </div>
            </x-ui.card.content>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4 xl:gap-6">
        <x-ui.card variant="glass">
            <x-ui.card.header class="pb-6 border-b border-border">
                    <h1>{{ __('user.statistics.stats.clips-submitted.title') }}</h1>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8 gap-1 flex items-center">
                <x-lucide-video defer class="size-3 sm:size-4 md:size-6"/>
                {{ Number::abbreviate($clipsSubmitted ?? 0, maxPrecision: 1) }}
            </x-ui.card.content>
            <x-ui.card.footer>

            </x-ui.card.footer>
        </x-ui.card>
        <x-ui.card variant="glass">
            <x-ui.card.header class="pb-6 border-b border-border">
                    <h1>{{ __('user.statistics.stats.votes.title') }}</h1>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8 gap-1 flex items-center">
                <x-lucide-heart defer class="size-3 sm:size-4 md:size-6"/>
                {{ Number::abbreviate($votes ?? 0, maxPrecision: 1) }}
            </x-ui.card.content>
            <x-ui.card.footer>
                <x-ui.button variant="ghost" href="{{ route('user.statistics.votes') }}" class="border border-gray-200 dark:border-white/20">
                    <x-lucide-info/>
                    {{ __('user.statistics.stats.votes.details') }}
                </x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
        <x-ui.card variant="glass">
            <x-ui.card.header class="pb-6 border-b border-border">
                    <h1>{{ __('user.statistics.stats.votes-30days.title') }}</h1>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8 gap-1 flex items-center">
                <x-lucide-heart defer class="size-3 sm:size-4 md:size-6"/>
                {{ Number::abbreviate($votes30Days ?? 0, maxPrecision: 1) }}
            </x-ui.card.content>
            <x-ui.card.footer>
                <x-ui.button variant="ghost" href="{{ route('user.statistics.votes') }}" class="border border-gray-200 dark:border-white/20">
                    <x-lucide-info/>
                    {{ __('user.statistics.stats.votes-30days.details') }}
                </x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
    </div>
</x-layout>
