@props(['variant' => 'default'])
@php
    static $baseAlertClass = 'relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current';

    static $variants = [
        'default' => 'bg-background text-foreground',
        'destructive' => 'text-destructive-foreground [&>svg]:text-current *:data-[slot=alert-description]:text-destructive-foreground/80',
        'success' => 'border-emerald-200/60 bg-emerald-50 text-emerald-900 shadow-[0_0_24px_rgba(16,185,129,0.18)] [&>svg]:text-emerald-700 *:data-[slot=alert-description]:text-emerald-900/80 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-100 dark:[&>svg]:text-emerald-300 dark:*:data-[slot=alert-description]:text-emerald-100/80'
    ];
@endphp
<div
    data-slot="alert"
    role="alert"
    {{ $attributes->twMerge($baseAlertClass, $variants[$variant ?? 'default'] ?? $variants['default']) }}
>
    {{ $slot }}
</div>
