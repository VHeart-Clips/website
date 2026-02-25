@props(['items' => null, 'disabled' => false])

<div
    {{ $attributes->twMerge('inline-block relative') }}
    x-data="reportButton({ items: @js($items, JSON_THROW_ON_ERROR), label: '{{ __('reports.modal.button', ['reportable' => '__REPORTABLE_LABEL__']) }}', disabled: {{ $disabled ? 'true' : 'false' }} })"
    x-modelable="items"
>
    <div x-show="disabled || items.length === 0">
        <button
            type="button"
            disabled
            class="size-9 rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ease-out disabled:opacity-40 sm:size-11 inline-flex items-center justify-center"
        >
            <x-lucide-flag class="size-6 text-destructive/80" defer/>
        </button>
    </div>

    <template x-if="!disabled && items.length > 0">
        <div>
            <template x-if="items.length === 1">
                <button
                    type="button"
                    @click="report(items[0])"
                    class="size-9 rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ease-out hover:bg-black active:scale-95 disabled:opacity-40 sm:size-11 sm:hover:scale-110 inline-flex items-center justify-center"
                >
                    <x-lucide-flag class="size-6 text-destructive/80 hover:text-destructive" defer/>
                    <span class="sr-only" x-text="getLabel(items[0])"></span>
                </button>
            </template>

            <template x-if="items.length > 1">
                <div
                    @keydown.escape.window="open = false"
                    @click.outside="open = false"
                    data-slot="dropdown-menu"
                    {{ $attributes->twMerge('relative inline-block text-left') }}
                >
                    <x-ui.dropdown.trigger>
                        <button
                            type="button"
                            class="size-9 rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ease-out hover:bg-black active:scale-95 disabled:opacity-40 sm:size-11 sm:hover:scale-110 inline-flex items-center justify-center"
                        >
                            <x-lucide-flag class="size-6 text-destructive/80 hover:text-destructive" defer/>
                        </button>
                    </x-ui.dropdown.trigger>
                    <x-ui.dropdown.content>
                        <template x-for="item in items" :key="item.type + '-' + item.id">
                            <x-ui.dropdown.item
                                click="report(item)"
                                x-text="getLabel(item)"
                            />
                        </template>
                    </x-ui.dropdown.content>
                </div>
            </template>
        </div>
    </template>
</div>

@pushonce('elements', 'report-modal')
    <x-ui.report.modal/>
@endpushonce
