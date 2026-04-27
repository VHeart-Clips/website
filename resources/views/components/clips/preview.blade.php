@use(Illuminate\Support\Number)
<a
    href="{{ $clip->getClipUrl() }}"
    aria-label="Clip öffnen: {{ $clip->title }}"
    target="_blank"
    {{ $attributes->twMerge('block group focus-visible:ring-primary-500 relative aspect-video w-full overflow-hidden rounded-md bg-gray-200 outline-none focus-visible:ring-2 dark:bg-gray-800') }}
>
    <x-image cookieName="external-services" src="{{ $clip->thumbnail_url ?? '' }}" :fallback="Vite::asset('resources/images/webp/clips/no_thumbnail.webp')" class="aspect-video">
        <x-slot:placeholder class="animate-pulse">
            <x-lucide-video defer class="size-16 opacity-25" />
        </x-slot:placeholder>

        <x-slot:consent>
            <x-lucide-cookie class="size-12 opacity-60" />
            <p class="text-xs md:text-md text-center px-6 leading-relaxed md:opacity-0 group-hover:opacity-100 transition-all">
                {{ __('clips.preview.consent-required') }}
            </p>
        </x-slot:consent>

        <x-slot:error>
            <x-lucide-video-off defer class="size-16 opacity-25" />
        </x-slot:error>
    </x-image>

    <x-clips.preview.duration :duration="round($clip->duration)" />
    <x-clips.preview.votes :votes="$clip->absolute_votes" />

    <x-clips.preview.container class="right-2 bottom-1 sm:bottom-2 left-2 block">
        <x-clips.preview.tags :clip="$clip" />

        <h3 class="line-clamp-1 text-xs font-medium sm:text-sm xl:text-base">
            {{ $clip->title }}
        </h3>

        <div class="truncate text-foreground font-bold text-xs sm:text-sm">
            {{ $clip->owner?->name ?? $clip->broadcaster_id }}
        </div>
    </x-clips.preview.container>
</a>
