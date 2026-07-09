@props(['variant' => 'default'])
@php
    static $cardBaseClass = 'flex flex-col rounded-xl border shadow-sm py-2 md:py-4 xl:py-6';

    static $cardVariants = [
        'default' => 'bg-card text-card-foreground',
        'glass' => 'border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 shadow-xl shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:bg-none dark:ring-0 dark:shadow-purple-900/30',
        'glassNoShadow' => 'border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:bg-none dark:ring-0'
    ];
@endphp

<div
    data-slot="card"
    {{ $attributes->twMerge($cardBaseClass,$cardVariants[$variant ?? 'default'] ?? $cardVariants['default']) }}
>
    {{ $slot }}
</div>
