@props(['user' => null, 'count' => 0, 'countLabel' => '','rank' => '-', 'anonym' =>  false])
<div class="py-2">
    <div class="ms-2 p-2 md:p-2 xl:p-2 flex items-center gap-2 ">

            <div class="text-center text-2xl w-[3ch]">
                {{ $rank }}.
            </div>

            @if($anonym)
                <x-ui.branding.logo class="h-14 w-14 shrink-0"></x-ui.branding.logo>
            @else
                <x-ui.avatar
                    class="h-14 w-14 shrink-0 border-2 border-white shadow-sm dark:border-white/10"
                    :force="true"
                    :src="Cookies::hasConsentFor('external-services') ? $user->avatar_url : null"
                    :name="$user->name ?? 'Unknown User'"
                />
            @endif
            <div>
                <div class="font-bold">
                    @if ($anonym)
                        <span class="blur-xs select-none">
                            Unknown User
                        </span>
                    @else
                        {{ $user->name }}
                    @endif
                </div>
                <div class="text-muted-foreground text-sm">
                    {{ $count }} {{ $countLabel }}
                </div>
            </div>
    </div>
</div>
