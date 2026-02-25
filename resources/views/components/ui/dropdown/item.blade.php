@props(['inset' => null, 'variant' => 'default', 'as' => null, 'click' => ''])

@php
    static $dropdownItemBaseClass = "w-full focus:bg-accent focus:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[inset=true]:pl-8 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4";
    static $dropdownDestructiveClass = "text-destructive-foreground hover:bg-destructive/10 focus:bg-destructive/10 dark:focus:bg-destructive/40 focus:text-destructive-foreground [&_svg]:!text-destructive-foreground";
    static $dropdownDefaultClass = "hover:bg-gray-100 hover:text-gray-900 focus:bg-gray-100 focus:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white dark:focus:bg-gray-800";

    $variantClasses = match ($variant) {
        'destructive' => $dropdownDestructiveClass,
        default => $dropdownDefaultClass,
    };

    $tag = $as ?? ($attributes->has('href') ? 'a' : 'button');
@endphp
<{{ $tag }}
    @if($inset) data-inset="true" @endif
    @if($attributes->has('disabled')) data-disabled @endif
    @click="open = false; {!! $click !!}"
    {{ $attributes->twMerge($dropdownItemBaseClass, $variantClasses)->except(['inset', 'variant', 'click']) }}
>
    {{ $slot }}
</{{ $tag }}>
