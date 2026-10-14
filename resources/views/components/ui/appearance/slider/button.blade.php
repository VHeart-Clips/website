@use(App\Enums\UserSettings\ThemePreference)
@use(Filament\Support\Enums\IconSize)
@props(['theme' => ThemePreference::System])
<button
    type="button"
    @click="updateAppearance(@js($theme, JSON_THROW_ON_ERROR))"
    :data-active="appearance === @js($theme, JSON_THROW_ON_ERROR)"
    :aria-pressed="appearance === @js($theme, JSON_THROW_ON_ERROR)"
    title="{{ __('Switch to :theme mode', ['theme' => $theme->getLabel()]) }}"
    class="relative z-10 inline-flex h-6 flex-1 items-center justify-center rounded-md transition-colors hover:text-neutral-900 dark:hover:text-neutral-50 dark:data-active:text-neutral-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400/60 dark:focus-visible:ring-neutral-500/60"
>
    <x-dynamic-component
        :component="$theme->getIcon()->getIconForSize(IconSize::Medium)"
        class="size-4"
        defer
    />
    <span class="sr-only">{{ $theme->getLabel() }}</span>
</button>
