<div
    @click="open = !open"
    aria-haspopup="true"
    :aria-expanded="open"
    x-ref="dropdownTrigger"
    {{ $attributes->twMerge('cursor-pointer') }}
>
    {{ $slot }}
</div>
