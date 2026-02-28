@props(['active' => false])

<a
    @if($active) data-active="true" @endif
    {{ $attributes->twMerge('group relative flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium transition-colors duration-200 ease-in-out outline-hidden select-none sm:px-3 data-active:bg-accent/25 data-active:text-gray-900 text-gray-600 hover:bg-accent/25 hover:text-gray-900 focus-visible:bg-accent/25 focus-visible:text-gray-900 focus-visible:ring-2 focus-visible:ring-accent/50 dark:text-white/70 dark:data-active:text-white dark:hover:text-white dark:focus-visible:text-white') }}
>
    @if (isset($icon))
        <div class="size-5 sm:size-4 *:size-full shrink-0 transition-transform duration-200 group-hover:scale-110 group-data-[active=true]:scale-110">
            {{ $icon }}
        </div>

        @if($slot->hasActualContent())
            <span class="hidden lg:inline">
                {{ $slot }}
            </span>
        @endif
    @else
        <span>
            {{ $slot }}
        </span>
    @endif
</a>
