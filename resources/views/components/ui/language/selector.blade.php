@php
    $currentLanguage = config('app.locales')[app()->currentLocale()] ?? config('app.locales')[app()->getFallbackLocale()];
@endphp
<x-ui.dropdown>
    <x-ui.dropdown.trigger>
        <x-ui.button variant="ghost">
            <x-dynamic-component :component="'flag-country-' . $currentLanguage['flag']" defer class="size-4" />
            <span>{{ $currentLanguage['name'] }}</span>
            <x-lucide-chevron-up defer class="size-4 transition-transform" x-bind:class="{ 'rotate-180': open }" />
        </x-ui.button>
    </x-ui.dropdown.trigger>
    <x-ui.dropdown.content>
        @foreach(config('app.locales') as $locale => $config)
            <x-ui.dropdown.item class="justify-between" href="{{ route('locales', ['locale' => $locale]) }}">
                <span class="flex flex-row gap-2 items-center">
                    <span><x-dynamic-component :component="'flag-country-' . $config['flag']" defer class="size-6" /></span>
                    <span>{{ $config['name'] }}</span>
                </span>
                @if($locale === app()->getLocale())
                    <x-lucide-check defer class="size-4" />
                @endif
            </x-ui.dropdown.item>
        @endforeach
    </x-ui.dropdown.content>
</x-ui.dropdown>
