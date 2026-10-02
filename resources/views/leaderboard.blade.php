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
            </x-ui.card>

            <div class="flex flex-col gap-4">
                @foreach ($topSubmitters as $topSubmitter)
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
                                    {{ $topSubmitter->submitted_clips_count }} leaderboard.submitter.count
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
            </x-ui.card>

            <div class="flex flex-col gap-4">
                @foreach ($topVoters as $topVoter)
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
                                    {{ $topVoter->votes_count }} leaderboard.voter.count
                                </div>
                            </div>
                    </x-ui.card.content>
                </x-ui.card>
                @endforeach
            </div>
        </div>
    </div>

</x-layout>
