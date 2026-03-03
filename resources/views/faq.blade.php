<x-layout :title="__('faq.title')">
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>
                <h1 class="text-center text-3xl font-bold">
                    <span class="bg-linear-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">{{ __('faq.title') }}</span>
                </h1>
            </x-ui.card.title>
        </x-ui.card.header>
        <x-ui.card.content
            x-data="faqSearch"
            class="space-y-6"
        >
            @if($questions->isNotEmpty() || request('search') !== null)
                <form method="GET" @submit.prevent>
                    <div class="relative">
                        <x-lucide-search class="size-5 text-muted-foreground pointer-events-none absolute inset-y-0 left-4 self-center" aria-hidden="true"/>
                        <x-ui.input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            x-model.debounce.250ms="search"
                            placeholder="{{ __('faq.search-placeholder') }}"
                            aria-label="{{ __('faq.search-placeholder') }}"
                            class="block w-full h-full rounded-xl py-3 pl-11 pr-4"
                        />
                    </div>
                </form>
            @endif

            <div class="space-y-2">
                @forelse($questions as $question)
                    <div x-show="isVisible('question-{{ $question->id }}')">
                        <x-faq.item id="question-{{ $question->id }}" :title="$question->title">
                            {{-- TODO: replace with markdown component later --}}
                            {!! \Illuminate\Support\Str::markdown($question->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                        </x-faq.item>
                    </div>
                @empty
                    <p class="text-center">
                        {{ __('faq.no-questions') }}
                    </p>
                @endforelse

                @if($questions->isNotEmpty() && request('search') === null)
                    <template x-if="matches < 1 && search.trim().length > 0">
                        <p class="text-center">
                            {{ __('faq.search-no-result') }}
                        </p>
                    </template>
                @endif
            </div>
        </x-ui.card.content>
    </x-ui.card>

    @push('elements')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('faqSearch', () => ({
                    search: '',
                    matches: 0,
                    init() {
                        this.$watch('search', () => {
                            this.matches = 0;
                        });
                    },
                    isVisible(id) {
                        if (this.search.trim() === '') return true;
                        const search = this.search.trim().toLowerCase();
                        const element = document.getElementById(id);
                        if (!element) return false;

                        const result = element.textContent.toLowerCase().includes(search);
                        if(result) {
                            this.matches++;
                            return true;
                        }
                        return false;
                    }
                }));
            })
        </script>
    @endpush
</x-layout>
