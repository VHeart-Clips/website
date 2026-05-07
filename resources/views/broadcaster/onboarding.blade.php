@use(App\Enums\Broadcaster\BroadcasterConsent)
@use(Illuminate\Support\Str)
@use(App\Enums\Clips\ClipStatus)
<x-layout :title="__('onboarding.title')" class="max-w-3xl w-full mx-auto font-sans">
    <form action="{{ route('dashboard.onboarding.store') }}" method="POST">
        <x-ui.card class="shadow-sm border border-border bg-card text-card-foreground">
            <x-ui.card.header class="pb-6 border-b border-border">
                <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                    <h1>{{ __('onboarding.heading', ['username' => auth()->user()->name]) }}</h1>
                </x-ui.card.title>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8">
                <div class="text-center space-y-2">
                    <p class="text-lg font-medium text-foreground">
                        {{ __('onboarding.setup.heading') }}
                    </p>
                </div>

                <section id="consent" class="space-y-5">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-foreground">
                            {{ __('onboarding.setup.consent.heading') }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ __('onboarding.setup.consent.subheading') }}
                        </p>
                    </div>

                    <div class="grid gap-3">
                        @foreach(BroadcasterConsent::cases() as $consentOption)
                            <x-onboarding.checkbox name="consent[]" :label="$consentOption->getLabel()"
                                                   :value="$consentOption->value"/>
                        @endforeach
                    </div>
                </section>


                <section id="default_clip_status" class="space-y-5">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-foreground">
                            {{ __('onboarding.setup.default_clip_status.heading') }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ __('onboarding.setup.default_clip_status.subheading') }}
                        </p>
                    </div>

                    <div class="grid gap-3">
                        @foreach(ClipStatus::defaultableOptions() as $option)
                            <x-onboarding.radio
                                name="default_clip_status"
                                label="{{ $option->getLabel() }}"
                                description="{{ __('onboarding.setup.default_clip_status.options.' . Str::snake($option->name)) }}"
                                value="{{ $option->value }}"
                            />
                        @endforeach
                    </div>
                </section>

                <section id="submit_permissions" class="space-y-5">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold text-foreground">
                            {{ __('onboarding.setup.submissions.heading') }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ __('onboarding.setup.submissions.subheading') }}
                        </p>
                    </div>

                    <div class="grid gap-3" x-data="{ everyone: true, vips: true, mods: true }">
                        <x-onboarding.checkbox
                            x-model="everyone"
                            @change="if(everyone) { vips = true; mods = true }"
                            checked name="everyone"
                            label="{{ __('onboarding.setup.submissions.options.everyone.label') }}"
                            description="{{ __('onboarding.setup.submissions.options.everyone.description') }}"
                            value="1"
                        />

                        {{-- https://github.com/VHeart-Clips/website/issues/714
                        <x-onboarding.checkbox
                            x-model="vips"
                            x-bind:data-everyone="everyone"
                            @change="if(!vips) everyone = false"
                            checked
                            name="vips"
                            label="{{ __('onboarding.setup.submissions.options.vips.label') }}"
                            description="{{ __('onboarding.setup.submissions.options.vips.description') }}"
                            value="1"
                        />
                        --}}

                        <x-onboarding.checkbox
                            x-model="mods"
                            x-bind:data-everyone="everyone"
                            @change="if(!mods) everyone = false"
                            checked
                            name="moderators"
                            label="{{ __('onboarding.setup.submissions.options.mods.label') }}"
                            description="{{ __('onboarding.setup.submissions.options.mods.description') }}"
                            value="1"
                        />
                    </div>
                </section>
            </x-ui.card.content>

            <x-ui.card.footer
                class="p-4 flex-col-reverse sm:flex-row justify-between md:justify-end gap-4 border-t border-border md:bottom-14 md:sticky md:z-10 bg-card text-card-foreground">
                <x-ui.button type="submit" name="action" value="skip" variant="outline">
                    {{ __('onboarding.setup.later') }}
                </x-ui.button>
                <x-ui.button type="submit" name="action" value="setup">
                    {{ __('onboarding.setup.submit') }}
                </x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
    </form>
</x-layout>
