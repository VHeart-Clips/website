@props(['state' => 'system', 'icon' => 'lucide-info'])
<button
    type="button"
    @click="updateAppearance('{{ $state }}')"
    :data-active="appearance === '{{ $state }}'"
    :aria-pressed="appearance === '{{ $state }}'"
    class="relative z-10 inline-flex h-6 flex-1 items-center justify-center rounded-md transition-colors text-neutral-500 hover:text-neutral-900 data-active:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-50 dark:data-active:text-neutral-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400/60 dark:focus-visible:ring-neutral-500/60"
    title="Switch to {{ $state }} mode"
>
    <x-dynamic-component :component="$icon" defer class="size-4" />
    <span class="sr-only">{{ $state }} mode</span>
</button>
