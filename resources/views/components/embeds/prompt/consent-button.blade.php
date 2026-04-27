<button
    @click="accept()"
    {{ $attributes->twMerge('rounded bg-zinc-600 px-2 py-1 md:px-4 md:py-2 font-bold text-white transition hover:bg-zinc-500 hover:text-white cursor-pointer') }}
>
    {{ $slot }}
</button>
