@use(App\Enums\FeatureFlag)
@use(Filament\Facades\Filament)
@use(App\Enums\UserSettings\NewGameAvoidancePreference)
<x-layout class="max-w-3xl w-full mx-auto space-y-6" :title="__('user.settings.title')">
    @feature(FeatureFlag::UserDashboard)
    <x-ui.alert>
        <x-ui.alert.title>
            {{ __('user.settings.broadcaster_note.label') }}
        </x-ui.alert.title>
        <x-ui.alert.description class="flex flex-col md:flex-row items-center justify-between">
            <p>
                {{ __('user.settings.broadcaster_note.description') }}
            </p>
            <x-ui.button href="{{ Filament::getPanel('dashboard')->getUrl() }}" variant="link">
                        <span class="md:hidden">
                            {{ __('user.settings.broadcaster_note.link') }}
                        </span>
                <x-lucide-external-link defer class="size-5"/>
            </x-ui.button>
        </x-ui.alert.description>
    </x-ui.alert>
    @endfeature

    <x-ui.card class="shadow-sm">
        <x-ui.card.header class="pb-6 border-b border-border">
            <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                <h1>{{ __('user.settings.preferences.heading') }}</h1>
            </x-ui.card.title>
        </x-ui.card.header>

        <x-ui.card.content class="p-4 pt-6 space-y-8">
            <section id="preferences" class="space-y-6">
                <form method="post" action="{{ route('user.settings.preferences.update') }}" class="space-y-6">
                    @method('put')

                    <!-- show_on_leaderboard -->
                    <label for="show_on_leaderboard" class="flex items-center justify-between cursor-pointer group">
                        <div class="space-y-0.5 pr-4">
                            <span class="text-sm font-medium text-foreground">
                                {{ __('user.settings.preferences.show_on_leaderboard.label') }}
                            </span>
                            <p class="text-sm text-muted-foreground">
                                {{ __('user.settings.preferences.show_on_leaderboard.description') }}
                            </p>
                        </div>
                        <x-ui.input.checkbox
                            id="show_on_leaderboard"
                            name="show_on_leaderboard"
                            value="1"
                            class="size-6"
                            :checked="auth()->user()->showsOnLeaderboardPreference()"
                        />
                    </label>

                    <!-- avoid_spoilers, TODO: remove hidden when implemented -->
                    <label for="avoid_spoilers" class="flex items-center justify-between cursor-pointer group hidden">
                        <div class="space-y-0.5 pr-4">
                            <span class="text-sm font-medium text-foreground">
                                {{ __('user.settings.preferences.avoid_spoilers.label') }}
                            </span>
                            <p class="text-sm text-muted-foreground">
                                {{ __('user.settings.preferences.avoid_spoilers.description') }}
                            </p>
                        </div>
                        <x-ui.input.checkbox
                            id="avoid_spoilers"
                            name="avoid_spoilers"
                            value="1"
                            class="size-6"
                            :checked="auth()->user()->avoidsSpoilersPreference()"
                        />
                    </label>

                    <!-- avoid_new_games, TODO: remove hidden when implemented -->
                    <div class="flex items-center justify-between gap-6 hidden">
                        <div class="space-y-0.5 pr-4">
                            <label for="avoid_new_games" class="text-sm font-medium text-foreground cursor-pointer">
                                {{ __('user.settings.preferences.avoid_new_games.label') }}
                            </label>
                            <p class="text-sm text-muted-foreground">
                                {{ __('user.settings.preferences.avoid_new_games.description') }}
                            </p>
                        </div>
                        <div class="w-72 shrink-0 space-y-1">
                            <x-ui.select id="avoid_new_games" name="avoid_new_games">
                                <option value="">
                                    {{ __('user.settings.preferences.avoid_new_games.options.none') }}
                                </option>
                                @foreach(NewGameAvoidancePreference::cases() as $case)
                                    <option
                                        value="{{ $case->value }}"
                                        @selected(auth()->user()->avoidNewGamesPreference() === $case)
                                    >
                                        {{ $case->getLabel() }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-ui.button type="submit">
                            {{ __('user.settings.preferences.submit') }}
                        </x-ui.button>
                    </div>
                </form>
            </section>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card class="shadow-sm">
        <x-ui.card.header class="pb-6 border-b border-border">
            <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                <h1>{{ __('user.settings.data-export.heading') }}</h1>
            </x-ui.card.title>
        </x-ui.card.header>

        <x-ui.card.content class="p-4 pt-6 space-y-8">
            <section id="data-export" class="space-y-6">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-foreground">
                        {{ __('user.settings.data-export.subheading') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ __('user.settings.data-export.description') }}
                    </p>
                </div>

                <form method="post" action="{{ route('user.settings.data-export') }}" class="space-y-6">
                    <div class="flex items-center gap-4">
                        <x-ui.button type="submit" variant="outline">
                            {{ __('user.settings.data-export.submit') }}
                        </x-ui.button>
                    </div>
                </form>
            </section>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card class="shadow-sm">
        <x-ui.card.header class="pb-6 border-b border-border">
            <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                <h1>{{ __('user.settings.delete.heading') }}</h1>
            </x-ui.card.title>
        </x-ui.card.header>

        <x-ui.card.content class="p-4 pt-6 space-y-8">
            <section id="consent" class="space-y-6">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-foreground">
                        {{ __('user.settings.delete.subheading') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ __('user.settings.delete.description') }}
                    </p>
                </div>

                <form method="post" action="{{ route('user.settings.delete') }}" class="space-y-6">
                    @method('delete')

                    @if($useTwoFactor)
                        <div class="space-y-2" x-data="twoFactorInput">
                            <label for="code" class="block text-sm font-medium text-foreground">
                                {{ __('user.settings.delete.confirmation.two-factor.label') }}
                                Gib zur Bestätigung deinen Zwei-Faktor Code ein
                            </label>
                            <x-ui.input.two-factor/>
                            <x-ui.button.two-factor-toggle/>
                        </div>

                        @pushonce('elements')
                            <script>
                                document.addEventListener('alpine:init', () => {
                                    Alpine.data('twoFactorInput', () => ({
                                        mode: '{{ request('mode', 'otp') }}',
                                        labels: {
                                            codeLabel: '{{ __('auth.two-factor.form.code.label') }}',
                                            backupLabel: '{{ __('auth.two-factor.form.backup.label') }}',
                                            toggleOtpLabel: '{{ __('auth.two-factor.form.mode-toggle.otp.label') }}',
                                            toggleBackupLabel: '{{ __('auth.two-factor.form.mode-toggle.backup.label') }}'
                                        },

                                        init() {
                                            this.$watch('mode', value => {
                                                const url = new URL(window.location);
                                                url.searchParams.set('mode', value);
                                                window.history.replaceState(null, '', url.toString());
                                            });
                                        },

                                        toggleMode() {
                                            this.mode = this.mode === 'otp' ? 'backup' : 'otp';
                                            this.$refs.twoFactorCodeInput.value = null;
                                            this.$nextTick(() => {
                                                this.$refs.twoFactorCodeInput.focus();
                                            });
                                        }
                                    }));
                                });
                            </script>
                        @endpushonce
                    @else
                        <div class="space-y-2">
                            <label for="code"
                                   class="block text-sm font-medium [&_code]:font-mono [&_code]:border [&_code]:px-1 [&_code]:py-0.5 [&_code]:rounded [&_code]:bg-muted [&_code]:select-none">
                                {!! __('user.settings.delete.confirmation.keyword.label', ['keyword' => __('user.settings.delete.confirmation.keyword.keyword')]) !!}
                            </label>
                            <x-ui.input id="code" name="code" type="text" required/>
                            @error('code')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="flex items-center gap-4">
                        <x-ui.button type="submit" variant="destructive">
                            {{ __('user.settings.delete.submit') }}
                        </x-ui.button>
                    </div>
                </form>
            </section>
        </x-ui.card.content>
    </x-ui.card>
</x-layout>
