@props(['label' => 'External Link', 'href' => '#', 'icon' => 'lucide-info', 'lightColor' => '#000', 'darkColor' => '#fff', 'iconSize' => 'size-6'])
<a
    href="{{ $href }}"
    title="{{ __('navigation.our-social', ['label' => $label]) }}"
    target="_blank"
    style="--h-color: {{ $lightColor }}; --h-color-dark: {{ $darkColor }};"
    {{ $attributes->twMerge("text-gray-500 transition-colors hover:text-(--h-color) dark:hover:text-(--h-color-dark) p-2") }}
>
    <x-dynamic-component :component="$icon" defer class="{{ $iconSize }}" />
    <span class="sr-only">{{ __('navigation.our-social', ['label' => $label]) }}</span>
</a>
