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
            <x-embeds.twitch x-model="currentClipId"/>

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

                            @error('clip_url')
                            <p class="text-sm font-medium text-destructive mt-2">{{ $message }}</p>
                            @enderror
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

</x-layout>
