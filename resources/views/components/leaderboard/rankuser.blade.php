@props(['user' => null, 'count' => 0, 'countLabel' => '','rank' => '-'])
<x-ui.card variant="glassNoShadow">
    <x-ui.card.content class="ms-2 p-2 md:p-2 xl:p-2 flex items-center gap-2 ">

            <div class="text-center text-2xl w-[3ch]">
                {{ $rank }}.
            </div>

            <x-ui.avatar
                class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
                :force="true"
                :src="Cookies::hasConsentFor('external-services') ? $user->avatar_url : null"
                :name="$user->name ?? 'Unknown User'"
            />
            <div>
                <div class="font-bold">
                    {{ $user->name }}
                </div>
                <div class="text-muted-foreground text-sm">
                    {{ $count }} {{ $countLabel }}
                </div>
            </div>
    </x-ui.card.content>
</x-ui.card>
