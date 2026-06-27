@use(App\Filament\Dashboard\Pages\Onboarding)
@use(App\Models\Broadcaster\Broadcaster)
@if (session('showTwitchPermissionsPrompt'))
    <div
        class="sticky top-16 md:top-18 z-100"
        x-data="{ dismissed: false }"
        x-show="!dismissed"
    >
        <x-ui.alert variant="info" class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <x-lucide-info defer class="shrink-0 size-8 md:size-12 text-accent"/>

                <div>
                    <x-ui.alert.title>
                        {{ __('dashboard/onboarding.alert.heading') }}
                    </x-ui.alert.title>

                    <x-ui.alert.description>
                        <p>{{ __('dashboard/onboarding.alert.description') }}</p>
                        <x-ui.button
                            variant="link"
                            href="{{ Onboarding::getUrl(panel: 'dashboard', tenant: Broadcaster::placeholder(auth()->id())) }}"
                        >
                            {{ __('dashboard/onboarding.alert.cta') }}
                        </x-ui.button>
                    </x-ui.alert.description>
                </div>
            </div>

            <x-ui.button
                style="display: none;"
                x-show="true"
                size="icon"
                variant="ghost"
                type="button"
                @click="dismissed = true"
            >
                <span class="sr-only">{{ __('dashboard/onboarding.alert.dismiss') }}</span>
                <x-lucide-x defer class="size-6"/>
            </x-ui.button>
        </x-ui.alert>
    </div>
@endif
