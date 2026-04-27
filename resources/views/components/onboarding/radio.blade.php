@props([
    'name' => 'checkbox',
    'label' => 'label',
    'value' => null,
    'description' => null,
])
<label
    class="relative flex items-center gap-4 p-4 rounded-md border border-border bg-card hover:bg-accent/20 hover:text-accent-foreground cursor-pointer transition-colors has-focus-visible:ring-1 has-focus-visible:ring-ring has-focus-visible:ring-offset-1 has-focus-visible:ring-offset-background active:bg-accent/30 has-data-[everyone='true']:opacity-50"
>
    <input
        type="radio"
        name="{{ $name }}"
        value="{{ $value }}"
        class="size-5 accent-accent data-[all='true']:accent-muted-foreground border-input rounded-sm focus:outline-none focus-visible:ring-ring bg-background cursor-pointer data-[everyone='true']:cursor-not-allowed"
        {{ $attributes }}
    >

    <div class="flex-1">
        <span class="block text-sm font-medium select-none">
            {{ $label }}
        </span>

        @if($description)
            <span class="block text-xs text-muted-foreground mt-1 select-none">
                {{ $description }}
            </span>
        @endif
    </div>
</label>
