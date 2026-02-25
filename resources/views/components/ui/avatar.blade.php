@props(['src' => null, 'name' => 'Guest', 'force' => false])

<div {{ $attributes->twMerge('relative flex shrink-0 overflow-hidden rounded-full border border-gray-200 dark:border-white/10') }}>
    @if($src)
        <x-image :src="$src" :alt="$name" class="aspect-square h-full w-full object-cover bg-white dark:bg-black" :force="$force" />
    @else
        <div class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
            {{ mb_strtoupper(collect(explode(' ', $name))->map(fn($s) => mb_substr($s, 0, 1))->take(2)->join('')) }}
        </div>
    @endif
</div>
