@props(['user' => null, 'count' => 0, 'countLabel' => '','croneColor' => 'text-primary'])
<div class="flex justify-center mb-1 {{ $croneColor }}">
    <x-lucide-crown class="size-12" defer />
</div>
<x-ui.card variant="glass">
    <x-ui.card.content class="p-2 md:p-2 xl:p-2 flex flex-col justify-center items-center gap-3 ">
        @if (isset($user))
            <x-ui.avatar
                class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
                :force="true"
                :src="Cookies::hasConsentFor('external-services') ? $user->avatar_url : null"
                :name="$user->name"
            />

            <div>
                <div class="text-center">
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
                </div>
                <div class="text-muted-foreground text-sm">
                    {{ $count }} {{ $countLabel }}
                </div>
            </div>
        @else
            <x-ui.branding.logo class="h-14 w-14 shrink-0"></x-ui.branding.logo>


            <div>
                <div class="text-center font-bold">
                    VHeart
                </div>
                <div class="text-muted-foreground text-sm">
                    - {{  $countLabel }}
                </div>
            </div>
        @endif
    </x-ui.card.content>
</x-ui.card>
