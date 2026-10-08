@props(['user' => null, 'count' => 0, 'countLabel' => '','crownColor' => 'text-primary', 'anonym' => false])
<div {{ $attributes->merge(['class' => "flex justify-center mb-1  $crownColor"]) }}>
    <x-lucide-crown class="size-9 lg:size-12 drop-shadow-md drop-shadow-black/42" defer />
</div>
<x-ui.card variant="glass">
    <x-ui.card.content class="p-2 md:p-2 xl:p-2 flex flex-col justify-center items-center gap-3 ">
        @if (isset($user) && !$anonym)
            <x-ui.avatar
                class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
                :force="true"
                :src="Cookies::hasConsentFor('external-services') ? $user->avatar_url : null"
                :name="$user->name"
            />
        @else
            <x-ui.branding.logo class="h-14 w-14 shrink-0"></x-ui.branding.logo>
        @endif

        <div>
            <div class="text-center">
                @if(isset($user))
                    @if($anonym)
                        <span class="blur-xs select-none">
                            Unknown User
                        </span>
                    @else
                        @if(mb_strlen($user->name) > 16)
                            <x-ui.tooltip>
                                <x-ui.tooltip.trigger>
                                    <span class="font-bold truncate max-w-[16ch]">
                                        {{ $user->name }}
                                    </span>
                                </x-ui.tooltip.trigger>
                                <x-ui.tooltip.content side="top">
                                    {{ $user->name }}
                                </x-ui.tooltip.content>
                            </x-ui.tooltip>
                        @else
                            <span class="text-center font-bold">
                                {{ $user->name }}
                            </span>
                        @endif
                    @endif
                @else
                    <div class="text-center font-bold">
                        {{ __('leaderboard.free') }}
                    </div>
                @endif
            </div>
                @if (isset($user))
                    <div class="text-muted-foreground text-sm">
                        {{ $count }} {{ __($countLabel . ($count > 1 ? '.plural' : '.singular')) }}
                    </div>
                @else
                    <div class="text-muted-foreground text-sm opacity-0 select-none" aria-hidden="true">
                        {{ __('leaderboard.free') }}
                    </div>
                @endif
            </div>

    </x-ui.card.content>
</x-ui.card>
