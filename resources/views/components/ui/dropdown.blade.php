<div
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    data-slot="dropdown-menu"
    {{ $attributes->twMerge('relative inline-block text-left') }}
>
    {{ $slot }}
</div>
