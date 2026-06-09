<x-layout :title="__('clips.submit.page_title')" class="max-w-7xl mx-auto py-8" x-data="clipPreview()">
    @if(session('submit_ok') && session('submit_message'))
        <x-ui.alert variant="success" class="mb-6">
            <x-lucide-check-circle defer class="shrink-0 mt-0.5 mr-3"/>
            <x-ui.alert.description>
                {{ session('submit_message') }}
            </x-ui.alert.description>
        </x-ui.alert>
    @endif

    @if($errors->has('error'))
        <x-ui.alert variant="destructive" class="mb-6">
            <x-lucide-alert-circle defer class="shrink-0 mt-0.5 mr-3"/>
            <x-ui.alert.description>
                {{ $errors->first('error') }}
            </x-ui.alert.description>
        </x-ui.alert>
    @endif

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div
                class="w-full aspect-video relative bg-black rounded-xl border border-muted shadow-sm overflow-hidden select-none">
                @if($ban)
                    <div class="flex flex-col items-center gap-3 text-center h-full justify-center">
                        <x-lucide-ban defer class="size-20 text-destructive"/>

                        <div class="tracking-wide antialiased text-balance md:text-base 4xl:text-lg p-1">
                            <h1 class="font-bold text-gray-200">{{ __('clips.submit.ban.heading') }}</h1>
                            <p class="text-gray-300">
                                @if($ban->banned_until)
                                    {{ __('clips.submit.ban.length.temporary', ['time' => $ban->banned_until->since()]) }}
                                @else
                                    {{ __('clips.submit.ban.length.permanent') }}
                                @endif
                            </p>
                            <p class="text-gray-400 mt-4 text-sm md:text-base">
                                {{ __('clips.submit.ban.any-questions') }}
                                <a href="https://go.vheart.net/discord" target="_blank"
                                   class="font-medium underline underline-offset-2 hover:opacity-75">
                                    {{ __('clips.submit.ban.discord') }}
                                </a>
                            </p>
                        </div>
                    </div>
                @else
                    <x-embeds.twitch x-model="currentClipId"/>
                @endif
            </div>

            @if(! $ban)
                <x-ui.card>
                    <x-ui.card.header>
                        <x-ui.card.title>
                            <h3>{{ __('clips.submit.form.heading') }}</h3>
                        </x-ui.card.title>
                    </x-ui.card.header>

                    <x-ui.card.content>
                        <form action="{{ route('submitclip.store') }}" method="POST" class="space-y-6">
                            @csrf

                            <div class="space-y-2">
                                <label for="clip_url" class="text-sm font-medium leading-none">
                                    {{ __('clips.submit.form.fields.clip_url.label') }}
                                </label>

                                <x-ui.input
                                    type="url"
                                    id="clip_url"
                                    name="clip_url"
                                    value="{{ old('clip_url') }}"
                                    @input="handleInput"
                                    @keydown.enter.prevent
                                    required
                                    placeholder="{{ __('clips.submit.form.fields.clip_url.placeholder') }}"
                                    autocomplete="off"
                                />

                                <x-ui.input.error>
                                    @error('clip_url') {{ $message }} @enderror
                                </x-ui.input.error>
                                <x-ui.input.error>
                                    {{ session('error') }}
                                </x-ui.input.error>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none">
                                    {{ __('clips.submit.form.fields.tags.label') }}
                                </label>
                                <p class="text-sm text-muted-foreground">
                                    {{ __('clips.submit.form.fields.tags.description') }}
                                </p>

                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($tags as $tag)
                                        <label
                                            class="cursor-pointer"
                                            x-bind:class="{ 'cursor-not-allowed': selectedTags.length >= 3 && !selectedTags.includes('{{ $tag->id }}') }"
                                        >
                                            <input
                                                type="checkbox"
                                                name="tags[]"
                                                value="{{ $tag->id }}"
                                                x-model="selectedTags"
                                                x-bind:disabled="selectedTags.length >= 3 && !selectedTags.includes('{{ $tag->id }}')"
                                                class="peer sr-only"
                                                @checked(is_array(old('tags')) && in_array($tag->id, old('tags'), false))
                                                autocomplete="off"

                                            >
                                            <div
                                                class="select-none inline-flex items-center rounded-md border border-input bg-background px-3 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted/20 peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background peer-checked:border-accent peer-checked:bg-accent/20 peer-checked:text-accent-foreground peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"
                                            >
                                                {{ $tag->name }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                @error('tags')
                                <p class="text-sm font-medium text-destructive mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-ui.button
                                class="w-full"
                                type="submit"
                                x-bind:disabled="!isValid()"
                                x-bind:class="{ 'opacity-50 cursor-not-allowed':! isValid() }"
                            >
                                {{ __('clips.submit.form.submit') }}
                            </x-ui.button>
                        </form>
                    </x-ui.card.content>
                </x-ui.card>
            @endif
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>
                        <h3>{{ __('clips.submit.aside.rules.heading') }}</h3>
                    </x-ui.card.title>
                </x-ui.card.header>

                <x-ui.card.content class="prose prose-sm dark:prose-invert marker:text-foreground">
                    <ul class="my-2">
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.rules.items.max_age', ['age' => config('vheart.clips.submission.maximum_age')]) }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.rules.items.minimum_duration', ['duration' => config('vheart.clips.submission.minimum_length')]) }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.rules.items.registered') }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.rules.items.consent') }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.rules.items.no_explicit') }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.rules.items.tags_match') }}</span>
                        </li>
                    </ul>
                </x-ui.card.content>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>
                        <h3>{{ __('clips.submit.aside.tips.heading') }}</h3>
                    </x-ui.card.title>
                </x-ui.card.header>

                <x-ui.card.content class="prose prose-sm dark:prose-invert marker:text-foreground">
                    <ul class="my-2">
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.tips.items.short') }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.tips.items.quality') }}</span>
                        </li>
                        <li class="my-1">
                            <span>{{ __('clips.submit.aside.tips.items.funny') }}</span>
                        </li>
                    </ul>
                </x-ui.card.content>
            </x-ui.card>
        </div>
    </div>


    @if(!$ban)
        @push('elements')
            <script>
                // i prefer 250ms debounce time for user input stuff, but in this case we trigger external stuff
                const DEBOUNCE_MS = 500;

                document.addEventListener('alpine:init', () => {
                    Alpine.data('clipPreview', () => ({
                        currentClipId: null,
                        timeout: null,
                        selectedTags: [],

                        init() {
                            const initialUrl = document.getElementById('clip_url')?.value || '';
                            this.extractClipId(initialUrl);

                            this.$nextTick(() => {
                                this.selectedTags = Array.from(document.querySelectorAll('input[name="tags[]"]:checked')).map(el => el.value);
                            });
                        },

                        isValid() {
                            return this.currentClipId && this.selectedTags.length > 0 && this.selectedTags.length <= 3;
                        },

                        handleInput(e) {
                            clearTimeout(this.timeout);
                            this.timeout = setTimeout(() => {
                                this.extractClipId(e.target.value);
                            }, DEBOUNCE_MS);
                        },

                        extractClipId(url) {
                            const match = url.match(/([A-Z][a-zA-Z0-9]*-[a-zA-Z0-9_-]+)/);
                            this.currentClipId = match ? match[0] : null;
                        }
                    }));
                })
            </script>
        @endpush
    @elseif($unbanInMs && $unbanInMs > 0 && $unbanInMs < 90_000)
        @push('elements')
            <script>
                setTimeout(() => location.reload(), {{ round($unbanInMs) + 2000 }});
            </script>
        @endpush
    @endif

</x-layout>
