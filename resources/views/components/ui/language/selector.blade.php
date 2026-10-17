@use(App\Enums\UserSettings\LanguagePreference)
@php
    $currentLanguage = LanguagePreference::tryFrom(app()->currentLocale()) ?? LanguagePreference::tryFrom(app()->getFallbackLocale());
@endphp
<x-ui.dropdown>
    <x-ui.dropdown.trigger>
        <x-ui.button variant="ghost">
            <x-dynamic-component :component="$currentLanguage->getIcon()" defer class="size-4"/>
            <span>{{ $currentLanguage->getLabel() }}</span>
            <x-lucide-chevron-up defer class="size-4 transition-transform" x-bind:class="{ 'rotate-180': open }"/>
        </x-ui.button>
    </x-ui.dropdown.trigger>
    <x-ui.dropdown.content>
        @foreach(LanguagePreference::cases() as $language)
            <x-ui.dropdown.item class="justify-between" href="{{ route('locales', ['locale' => $language]) }}">
                <span class="flex flex-row gap-2 items-center">
                    <span>
                        <x-dynamic-component :component="$language->getIcon()" defer class="size-6"/>
                    </span>
                    <span>{{ $language->getLabel() }}</span>
                </span>
                @if($language->value === app()->currentLocale())
                    <x-lucide-check defer class="size-4" />
                @endif
            </x-ui.dropdown.item>
        @endforeach
    </x-ui.dropdown.content>
</x-ui.dropdown>
