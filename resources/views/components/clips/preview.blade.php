<a
    href="{{ $clip->getClipUrl() }}"
    aria-label="Clip öffnen: {{ $clip->title }}"
    target="_blank"
    {{ $attributes->twMerge('block group focus-visible:ring-primary-500 relative aspect-video w-full overflow-hidden rounded-md bg-gray-200 outline-none focus-visible:ring-2 dark:bg-gray-800') }}
>
    <x-image src="{{ $clip->proxiedContentUrl() }}" class="aspect-video" />

    <div class="absolute top-2 left-2 flex items-center gap-1 rounded-lg bg-black/60 px-1.5 py-0.5 text-white backdrop-blur-[2px] transition-colors group-hover:bg-black/85 sm:px-2 sm:py-1 sm:text-xs">
        <x-lucide-clock class="size-3 sm:size-4 md:size-6" aria-hidden="true" defer />
        <p class="sr-only">Länge</p>
        <span class="font-mono text-sm">
         {{ round($clip->duration) }}
        </span>
    </div>

    <div class="absolute top-2 right-2 flex items-center gap-1 rounded-lg bg-black/60 px-1.5 py-0.5 text-white backdrop-blur-[2px] transition-colors group-hover:bg-black/85 sm:px-2 sm:py-1 sm:text-xs">
        <x-lucide-heart class="text-red-500 size-3 sm:size-4 md:size-6" aria-hidden="true" defer />
        <p class="sr-only">Stimmen</p>
        <span class="text-sm">
            {{ $clip->votes_count }}
        </span>
    </div>


    <div class="absolute right-2 bottom-1 left-2 rounded-xl bg-black/75 px-2 py-1 text-white backdrop-blur-[2px] transition-colors group-hover:bg-black/85 sm:bottom-2">
        <div class="line-clamp-1 text-xs font-medium sm:text-sm">
            {{ $clip->title }}
        </div>

        <div class="truncate text-white/80 sm:text-xs">
            {{ $clip->broadcaster->name }}
        </div>
    </div>
</a>
