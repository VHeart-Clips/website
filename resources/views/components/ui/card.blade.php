<div
    data-slot="card"
    {{ $attributes->twMerge('bg-card text-card-foreground flex flex-col rounded-xl border shadow-sm py-2 md:py-4 xl:py-6') }}
>
    {{ $slot }}
</div>
