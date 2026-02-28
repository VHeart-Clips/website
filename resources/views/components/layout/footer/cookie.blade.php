<form method="post" action="{{ route('cookieconsent.reset') }}" x-data @submit.prevent="LaravelCookieConsent?.reset();">
    <x-ui.button size="icon" title="{{__('navigation.manage-cookies')}}" variant="ghost" type="submit">
        <x-lucide-cookie defer class="size-4" />
        <span class="sr-only">{{__('navigation.manage-cookies')}}</span>
    </x-ui.button>
</form>
