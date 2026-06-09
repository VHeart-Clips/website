<x-filament::callout icon="lucide-ban" color="danger" class="mt-2">
    <x-slot name="heading">
        {{ __($ban->banned_until ? 'broadcaster.ban.heading.temporary' : 'broadcaster.ban.heading.permanent', ['name' => $tenant->name]) }}
    </x-slot>
    <x-slot name="description">
        {{ __('broadcaster.ban.description') }}
        @if($ban->banned_until)
            {{ __('broadcaster.ban.temporary', ['date' => $ban->banned_until->translatedFormat('d. F Y, H:i')]) }}
        @else
            {{ __('broadcaster.ban.permanent') }}
        @endif
        {{ __('broadcaster.ban.any-questions') }}
        <a href="https://go.vheart.net/discord" target="_blank"
           class="font-medium underline underline-offset-2 hover:opacity-75">
            {{ __('broadcaster.ban.discord') }}
        </a>
    </x-slot>
</x-filament::callout>
