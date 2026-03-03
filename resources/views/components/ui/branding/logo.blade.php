@use(Illuminate\Support\Facades\Vite)
<img
    src="{{ Vite::asset('resources/images/svg/logo-dark.svg') }}"
    alt="{{ __('navigation.logo_alt') }}"
    {{ $attributes->twMerge('hidden dark:block') }}
/>
<img
    src="{{ Vite::asset('resources/images/svg/logo-light.svg') }}"
    alt="{{ __('navigation.logo_alt') }}"
    {{ $attributes->twMerge('block dark:hidden') }}
/>
