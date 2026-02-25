<button
    @click="accept()"
    {{ $attributes->twMerge('text-md rounded bg-zinc-600 px-4 py-2 font-bold text-white transition hover:bg-zinc-500 hover:text-white cursor-pointer') }}
>
    {{ $slot }}
</button>
