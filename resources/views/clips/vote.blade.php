<x-layout
    :title="__('clips.vote.page_title')"
    style="--base-w: 32rem; --growth: 24; --max-w: 80rem;"
    class="md:w-[clamp(var(--base-w),calc(var(--base-w)+var(--growth)*((100svw-40rem)/60)),var(--max-w))] w-full mx-auto 2xl:pt-8 space-y-2 flex flex-col justify-center md:block"
    x-load
    x-data="clipVote({
        clipTwitchId: '{{ $clip?->twitch_id ?? '' }}',
        clipId: {{ $clip?->id ?? 'null' }},
        clipBroadcasterAvatar: '{{ $clip?->owner?->avatar_url ?? '' }}',
        clipBroadcasterUrl: 'https://twitch.tv/{{ $clip?->owner?->name ?? '' }}',
        clipBroadcasterName: '{{ $clip?->owner?->name ?? '' }}',
        hasBroadcaster: {{ $clip?->owner ? 'true' : 'false' }},
        hasClip: {{ $clip ? 'true' : 'false' }},
        votes: {{ $clip?->absolute_votes ?? 0 }},
        initialDuration: {{ $clip?->duration ?? 0 }},
        reportItems: {{ $clip ? '[{ type: \'clip\', id: ' . $clip->id . ' }]' : 'null' }},
    })"
>
    <section
        class="w-full aspect-video h-full relative bg-black rounded-xl border border-muted shadow-sm overflow-hidden select-none">

        @if($ban)
            <div class="flex flex-col items-center gap-3 text-center h-full justify-center">
                <x-lucide-ban defer class="size-20 text-destructive"/>

                <div class="tracking-wide antialiased text-balance md:text-base 4xl:text-lg">
                    <h1 class="font-bold text-gray-200">{{ __('clips.vote.ban.heading') }}</h1>
                    <p class="text-gray-300">
                        @if($ban->banned_until)
                            {{ __('clips.vote.ban.length.temporary', ['time' => $ban->banned_until->since()]) }}
                        @else
                            {{ __('clips.vote.ban.length.permanent') }}
                        @endif
                    </p>
                    <p class="text-gray-400 mt-4 text-sm md:text-base">
                        {{ __('clips.vote.ban.any-questions') }}
                    </p>
                </div>
            </div>
        @else
            <template x-if="hasClip">
                <x-embeds.twitch :clip="$clip?->twitch_id ?? ''" x-model="clipTwitchId" class="h-full w-full"/>
            </template>
            <template x-if="!hasClip">
                <div class="absolute inset-0 grid place-items-center text-sm text-foreground">
                    {{ __('clips.vote.aside.nothing_left') }}
                </div>
            </template>

            <x-noscript-block/>
        @endif
    </section>

    <section
        data-clip="false"
        data-maintenance="false"
        :data-clip="hasClip ? 'true' : 'false'"
        :data-maintenance="isMaintenanceMode ? 'true' : 'false'"
        class="sticky bottom-18 w-full max-w-3xl mx-auto flex flex-row items-center bg-white/75 dark:bg-black/80    border border-muted    ring-black/5 ring-1 dark:ring-0    backdrop-blur-md rounded-2xl    shadow-xl dark:shadow-none    transition-all duration-300 ease-out data-[clip=false]:opacity-0 data-[clip=false]:translate-y-4 data-[clip=false]:pointer-events-none"
    >
        <div
            data-shown="false"
            :data-shown="isMaintenanceMode ? 'true' : 'false'"
            class="absolute inset-0 z-20 rounded-2xl flex items-center justify-center gap-2 bg-white/95 dark:bg-black/95 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 data-[shown=true]:opacity-100 data-[shown=true]:pointer-events-auto"
        >
            <x-lucide-loader-circle class="size-4 shrink-0 animate-spin text-muted-foreground"/>
            <span class="text-sm text-muted-foreground">{{ __('clips.vote.maintenance') }}</span>
        </div>

        <div class="flex items-center gap-1 flex-1 justify-start sm:py-3 pl-2 sm:pl-4">
            <template x-if="hasBroadcaster">
                <a href="https://twitch.tv/{{ $clip->owner?->name ?? '' }}" x-bind:href="clipBroadcasterUrl"
                   target="_blank" class="flex items-center gap-1">
                    <img src="{{ $clip?->owner?->avatar_url ?? '' }}" alt="Avatar"
                         x-bind:src="clipBroadcasterAvatar" class="size-6 sm:size-8 rounded-full"/>
                    <span class="truncate max-w-26 sm:max-w-50"
                          x-text="clipBroadcasterName">{{ $clip->owner?->name ?? '' }}</span>
                </a>
            </template>
            <template x-if="!hasBroadcaster">
                <x-ui.branding.logo class="h-6 sm:h-8 rounded-full"/>
            </template>
        </div>

        <div class="flex shrink-0 items-center justify-center gap-3 py-2 sm:gap-4 sm:py-3">
            <div class="flex items-center gap-3 sm:gap-4">
                <div
                    data-loading="false"
                    :data-loading="isLoading ? 'true' : 'false'"
                    class="relative flex items-center gap-3 sm:gap-4 transition-opacity duration-200 data-[loading=true]:animate-pulse"
                >
                    <div
                        data-shown="true"
                        :data-shown="timeLeft > 0 ? 'true' : 'false'"
                        class="absolute -inset-1 z-10 flex items-center justify-center rounded-full bg-white/90 dark:bg-black/20    border border-muted    ring-black/5 ring-1 dark:ring-0    dark:backdrop-blur-md opacity-0 pointer-events-none transition-opacity duration-300 data-[shown=true]:opacity-100 data-[shown=true]:pointer-events-auto select-none"
                    >
                            <span
                                class="col-start-1 row-start-1 text-sm font-bold text-foreground sm:text-base font-mono"
                                x-text="Math.round(timeLeft)"></span>
                    </div>

                    <x-ui.button
                        variant="icon"
                        type="button"
                        @click="arm('like')"
                        x-bind:disabled="timeLeft > 0 || isLoading || !hasClip || isMaintenanceMode"
                        x-bind:data-armed="armedButton === 'like' ? 'true' : 'false'"
                        :disabled="!$clip"
                        :title="__('clips.vote.form.fields.vote.label')"
                        class="inline size-9 place-items-center rounded-full bg-accent/25 dark:bg-black ring-1 ring-white/10 sm:size-11 transition-all duration-150 ease-out active:scale-95 sm:hover:scale-110 sm:hover:text-destructive group relative before:absolute before:-inset-2 before:content-[''] before:rounded-full data-[armed=true]:scale-110 data-[armed=true]:ring-2 data-[armed=true]:ring-destructive data-[armed=true]:bg-destructive/10"
                    >
                        <x-lucide-heart defer
                                        class="size-4 sm:size-5 text-accent-foreground group-hover:text-destructive transition-colors group-data-[armed=true]:text-destructive group-data-[armed=true]:scale-110 group-data-[armed=true]:fill-current"/>
                        <span class="sr-only">{{ __('clips.vote.form.fields.vote.label') }}</span>
                    </x-ui.button>

                    <x-ui.button
                        variant="icon"
                        type="button"
                        @click="arm('skip')"
                        :disabled="!$clip"
                        x-bind:disabled="timeLeft > 0 || isLoading || !hasClip || isMaintenanceMode"
                        x-bind:data-armed="armedButton === 'skip' ? 'true' : 'false'"
                        :title="__('clips.vote.form.fields.skip.label')"
                        class="inline size-9 place-items-center rounded-full bg-accent/25 dark:bg-black ring-1 ring-white/10 sm:size-11 transition-all duration-150 ease-out active:scale-95 sm:hover:scale-110 group relative before:absolute before:-inset-2 before:content-[''] before:rounded-full data-[armed=true]:scale-110 data-[armed=true]:ring-2 data-[armed=true]:ring-muted-foreground data-[armed=true]:bg-muted/30"
                    >
                        <x-lucide-circle-x defer
                                           class="size-4 sm:size-5 text-accent-foreground group-hover:text-muted-foreground transition-colors group-data-[armed=true]:text-muted-foreground group-data-[armed=true]:scale-110"/>
                        <span class="sr-only">{{ __('clips.vote.form.fields.skip.label') }}</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        <div class="flex-1 flex justify-end p-2 sm:py-3 pr-2 sm:pr-4">
            <x-ui.report.button
                x-model="reportItems" :items="[]"
            />
        </div>
    </section>

    @if($ban && $unbanInMs && $unbanInMs > 0 && $unbanInMs < 90_000)
        @push('elements')
            <script>
                setTimeout(() => location.reload(), {{ round($unbanInMs) + 2000 }});
            </script>
        @endpush
    @endif
</x-layout>
