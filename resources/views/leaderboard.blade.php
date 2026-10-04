@use(Carbon\Carbon)
@php
    $rankColor = ["text-amber-300","text-neutral-400","text-amber-900"];
@endphp
<x-layout class="max-w-5xl w-full mx-auto space-y-6" :title="__('leaderboard.page_title')">
    <div class="m-auto py-8">
        <x-ui.card variant="glass">
            <x-ui.card.header class="pb-6 border-b border-border">
                <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                    <h1>{{ __('leaderboard.top.heading') }}</h1>
                </x-ui.card.title>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-foreground">
                        {{ __('leaderboard.top.subheading') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ __('leaderboard.top.description') }}
                    </p>
                </div>

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

            </x-ui.card.content>
        </x-ui.card>
    </div>

    <div class="mx-auto grid grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:gap-12 mb-8">

        <div class="grid grid-cols-1 gap-4">
            <x-ui.card variant="glass">
                <x-ui.card.header class="pb-6 border-b border-border">
                    <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                        <h1>{{ __('leaderboard.submitter.heading') }}</h1>
                    </x-ui.card.title>
                </x-ui.card.header>

                <x-ui.card.content class="p-4 pt-6 space-y-8">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-foreground">
                            {{ __('leaderboard.submitter.subheading') }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ __('leaderboard.submitter.description') }}
                        </p>
                    </div>
                </x-ui.card.content>

                <x-ui.card.footer class="p-2 md:p-2 xl:p-2 pe-5">
                    <div class="text-xs text-muted-foreground w-full flex justify-end gap-2 items-center">
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
                </x-ui.card.footer>
            </x-ui.card>

            <div class="flex flex-col gap-4">
                @foreach ($topSubmitters['users'] as $topSubmitter)
                <x-ui.card variant="glass">
                    <x-ui.card.content class="ms-2 p-4 md:p-2 xl:p-2 flex items-center gap-2 ">

                            @if ($loop->index < 3)
                                <x-lucide-trophy class="size-6 {{$rankColor[$loop->index] ?? ''}}"></x-lucide-trophy>
                            @else
                                <x-lucide-hash class="size-6"></x-lucide-hash>
                            @endif

                            <div class="text-center text-2xl w-[3ch]">
                                {{ $loop->index + 1 }}.
                            </div>

                            <x-ui.avatar
                                class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
                                :force="true"
                                :src="Cookies::hasConsentFor('external-services') ? $topSubmitter->avatar_url : null"
                                :name="$topSubmitter->name ?? 'Unknown User'"
                            />
                            <div>
                                <div class="font-bold">
                                    {{ $topSubmitter->name }}
                                </div>
                                <div class="text-muted-foreground text-sm">
                                    {{ $topSubmitter->submitted_clips_count }} {{ __('leaderboard.submitter.count') }}
                                </div>
                            </div>
                    </x-ui.card.content>
                </x-ui.card>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <x-ui.card variant="glass">
                <x-ui.card.header class="pb-6 border-b border-border">
                    <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                        <h1>{{ __('leaderboard.voter.heading') }}</h1>
                    </x-ui.card.title>
                </x-ui.card.header>

                <x-ui.card.content class="p-4 pt-6 space-y-8">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-foreground">
                            {{ __('leaderboard.voter.subheading') }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ __('leaderboard.voter.description') }}
                        </p>
                    </div>
                </x-ui.card.content>

                <x-ui.card.footer class="p-2 md:p-2 xl:p-2 pe-5">
                    <div class="text-xs text-muted-foreground w-full flex justify-end gap-2 items-center">
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
                </x-ui.card.footer>
            </x-ui.card>

            <div class="flex flex-col gap-4">
                @foreach ($topVoters["users"] as $topVoter)
                <x-ui.card variant="glass">
                    <x-ui.card.content class="ms-2 p-4 md:p-2 xl:p-2 flex items-center gap-2 ">

                            @if ($loop->index < 3)
                                <x-lucide-trophy class="size-6 {{$rankColor[$loop->index] ?? ''}}"></x-lucide-trophy>
                            @else
                                <x-lucide-hash class="size-6"></x-lucide-hash>
                            @endif

                            <div class="text-center text-2xl w-[3ch]">
                                {{ $loop->index + 1 }}.
                            </div>

                            <x-ui.avatar
                                class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
                                :force="true"
                                :src="Cookies::hasConsentFor('external-services') ? $topVoter->avatar_url : null"
                                :name="$topVoter->name ?? 'Unknown User'"
                            />
                            <div>
                                <div class="font-bold">
                                    {{ $topVoter->name }}
                                </div>
                                <div class="text-muted-foreground text-sm">
                                    {{ $topVoter->votes_count }} {{ __('leaderboard.voter.count') }}
                                </div>
                            </div>
                    </x-ui.card.content>
                </x-ui.card>
                @endforeach
            </div>
        </div>
    </div>

</x-layout>
