@use(Carbon\Carbon)
@php
    $rankColor = ["text-amber-300","text-neutral-400","text-amber-900"];
@endphp
<x-layout class="max-w-7xl w-full mx-auto space-y-6" :title="__('leaderboard.page_title')">
    <div class="m-auto py-8">
        <x-ui.card variant="glass">
            <x-ui.card.header>
                <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                    <h1>{{ __('leaderboard.top.heading') }}</h1>
                </x-ui.card.title>
            </x-ui.card.header>

            <x-ui.card.content class="p-2 md:p-2 xl:p-2 space-y-2">
                <div class="space-y-1 px-4">
                    <h3 class="text-base font-semibold text-foreground">
                        {{ __('leaderboard.top.subheading') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ __('leaderboard.top.description') }}
                    </p>
                </div>
            </x-ui.card.content>

            <x-ui.card.footer class="p-1 md:p-1 xl:p-1 pe-2 justify-end">
                <x-ui.dropdown>
                    <x-ui.dropdown.trigger>
                        <button
                            class="group inline-flex h-auto items-center gap-2 px-1 py-1 sm:px-2 sm:py-1.5 cursor-pointer rounded-xl outline-hidden select-none transition-colors duration-200 ease-in-out text-gray-600 hover:bg-accent/15 hover:text-gray-900 focus-visible:bg-accent/15 focus-visible:text-gray-900 focus-visible:ring-2 focus-visible:ring-accent/50 dark:text-white/70 dark:hover:text-white dark:focus-visible:text-white"
                        >

                            <span
                                class="hidden text-sm font-medium xl:inline"
                            >
                                {{ $selectedRange->getLabel() }}: {{ $selectedRange->getTimestampFrom()->format('d.m.Y') }} - {{ $selectedRange->getTimestampTo()->format('d.m.Y') }}
                            </span>

                            <x-lucide-chevron-down
                                class="hidden size-4 opacity-70 transition-transform duration-200 group-hover:scale-110 lg:block"
                                x-bind:class="{ 'rotate-180': open }"
                                defer
                            />
                        </button>
                    </x-ui.dropdown.trigger>

                    <x-ui.dropdown.content align="right" class="min-w-55">

                        @foreach ($ranges as $range)
                            <x-ui.dropdown.item :href="route('leaderboard',['range' => $range])">
                                {{$range->getLabel()}}
                            </x-ui.dropdown.item>
                        @endforeach

                    </x-ui.dropdown.content>
                </x-ui.dropdown>
                </x-ui.card.footer>
        </x-ui.card>
    </div>

    <div class="mx-auto grid grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:gap-12 mb-8">

        <div class="grid grid-cols-1">
            <div class="inline-flex w-full flex-col justify-center">
                <div>
                    <div class="flex justify-center items-center gap-2 text-2xl font-bold tracking-tight">
                        <x-lucide-send class="size-6" defer />
                        <h2>{{ __('leaderboard.submitter.heading') }}</h2>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-muted-foreground w-full inline-flex justify-center gap-2 items-center">
                        <x-ui.tooltip>
                            <x-ui.tooltip.trigger>
                                {{ $topSubmitters['timestamp']->diffForHumans() }}
                            </x-ui.tooltip.trigger>
                            <x-ui.tooltip.content side="bottom">
                                {{ $topSubmitters['timestamp']->format('d.m.Y H:i:s') }}
                            </x-ui.tooltip.content>
                        </x-ui.tooltip>
                        <x-lucide-clock class="size-3" defer/>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 grid-rows-1 gap-4 pt-10">
                <div class="col-start-2 row-start-1 animate-float">
                    <x-leaderboard.topuser
                            :user="$topSubmitters['users'][0] ?? null"
                            :count="$topSubmitters['users'][0]->submitted_clips_count ?? 0"
                            :countLabel="__('leaderboard.submitter.count')"
                            croneColor="text-amber-300"
                    ></x-leaderboard.topuser>
                </div>
                <div class="col-start-1 row-start-1 mt-6 animate-float" style='animation-delay: -250ms;'>
                    <x-leaderboard.topuser
                            :user="$topSubmitters['users'][1] ?? null"
                            :count="$topSubmitters['users'][1]->submitted_clips_count ?? 0"
                            :countLabel="__('leaderboard.submitter.count')"
                            croneColor="text-neutral-400"
                    ></x-leaderboard.topuser>
                </div>
                <div class="col-start-3 row-start-1 mt-12 animate-float" style='animation-delay: -750ms;'>
                    <x-leaderboard.topuser
                            :user="$topSubmitters['users'][2] ?? null"
                            :count="$topSubmitters['users'][2]->submitted_clips_count ?? 0"
                            :countLabel="__('leaderboard.submitter.count')"
                            croneColor="text-amber-900"
                    ></x-leaderboard.topuser>
                </div>
            </div>


            <div class="flex flex-col gap-4">
                @foreach ($topSubmitters['users']->skip(3) as $topSubmitter)
                    <x-leaderboard.rankuser
                        :user="$topSubmitter"
                        :count="$topSubmitter->submitted_clips_count ?? 0"
                        :countLabel="__('leaderboard.submitter.count')"
                        :rank="$loop->index + 4"
                    ></x-leaderboard.rankuser>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="inline-flex w-full flex-col justify-center">
                <div>
                    <div class="flex justify-center items-center gap-2 text-2xl font-bold tracking-tight">
                        <x-lucide-thumbs-up class="size-6" defer />
                        <h2>{{ __('leaderboard.voter.heading') }}</h2>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-muted-foreground w-full inline-flex justify-center gap-2 items-center">
                        <x-ui.tooltip>
                            <x-ui.tooltip.trigger>
                                {{ $topVoters['timestamp']->diffForHumans() }}
                            </x-ui.tooltip.trigger>
                            <x-ui.tooltip.content side="bottom">
                                {{ $topVoters['timestamp']->format('d.m.Y H:i:s') }}
                            </x-ui.tooltip.content>
                        </x-ui.tooltip>
                        <x-lucide-clock class="size-3" defer/>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 grid-rows-1 gap-4 pt-10  animate-float">
                <div class="col-start-2 row-start-1">
                    <x-leaderboard.topuser
                            :user="$topVoters['users'][0] ?? null"
                            :count="$topVoters['users'][0]->votes_count ?? 0"
                            :countLabel="__('leaderboard.voter.count')"
                            croneColor="text-amber-300"
                    ></x-leaderboard.topuser>
                </div>
                <div class="col-start-1 row-start-1 mt-6 animate-float" style='animation-delay: -250ms;'>
                    <x-leaderboard.topuser
                            :user="$topVoters['users'][1] ?? null"
                            :count="$topVoters['users'][1]->votes_count ?? 0"
                            :countLabel="__('leaderboard.voter.count')"
                            croneColor="text-neutral-400"
                    ></x-leaderboard.topuser>
                </div>
                <div class="col-start-3 row-start-1 mt-12 animate-float" style='animation-delay: -750ms;'>
                    <x-leaderboard.topuser
                            :user="$topVoters['users'][2] ?? null"
                            :count="$topVoters['users'][2]->votes_count ?? 0"
                            :countLabel="__('leaderboard.voter.count')"
                            croneColor="text-amber-900"
                    ></x-leaderboard.topuser>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                @foreach ($topVoters["users"]->skip(3) as $topVoter)
                    <x-leaderboard.rankuser
                        :user="$topVoter"
                        :count="$topVoter->votes_count ?? 0"
                        :countLabel="__('leaderboard.voter.count')"
                        :rank="$loop->index + 4"
                    ></x-leaderboard.rankuser>
                @endforeach
            </div>
        </div>
    </div>

</x-layout>
