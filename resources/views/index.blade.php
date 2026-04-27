<x-layout title="Startseite">
    <div class="m-auto px-4 py-8">
        <section
            style="--base-w: 32rem; --growth: 24; --max-w: 80rem;"
            class="shadow-2xl md:w-[clamp(var(--base-w),calc(var(--base-w)+var(--growth)*((100svw-40rem)/60)),var(--max-w))] w-full m-auto"
        >
            <x-embeds.youtube
                url="https://www.youtube-nocookie.com/embed/videoseries?list=PLPwib1xj01i4I_TqtyrRpnrjD2oaUknOn"
            />
            {{-- we could add some other info here later for a quick overview what we do or something with CTA or links to more resources --}}
        </section>

        {{-- too empty and it kinda breaks the illusion --}}
        @if($bestRated->isNotEmpty() && $bestRated->count() > 4)
            <section class="space-y-4 mt-12">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center">{{ __('index.clips.best-rated') }}</h2>

                <div
                    class="flex items-center w-full relative"
                    x-load="visible"
                    x-data="clipsSlider"
                >
                    <button
                        aria-label="Next Item"
                        @click="prev()"
                        x-bind:class="{ 'opacity-0 pointer-events-none': false }"
                        class="opacity-0 pointer-events-none absolute top-1/2 left-4 z-10 -translate-y-1/2 rounded-full bg-white/25 p-2 shadow transition-all hover:scale-110 hover:bg-accent active:scale-95 after:absolute after:-inset-2 after:content-['']"
                    >
                        <x-lucide-chevron-left class="size-5" defer/>
                    </button>

                    <div
                        x-ref="slider"
                        class="flex w-full overflow-x-hidden snap-x snap-mandatory"
                    >
                        @foreach($bestRated as $clip)
                            <div class="shrink-0 snap-start w-full sm:w-1/2 lg:w-1/3 p-2"
                                 id="slider-clip-{{ $clip->twitch_id }}">
                                <x-clips.preview :clip="$clip" class="w-full h-full"/>
                            </div>
                        @endforeach
                    </div>

                    <button
                        aria-label="Previous Item"
                        @click="next()"
                        x-bind:class="{ 'opacity-0 pointer-events-none': false }"
                        class="opacity-0 pointer-events-none absolute top-1/2 right-4 z-10 -translate-y-1/2 rounded-full bg-white/25 p-2 shadow transition-all hover:scale-110 hover:bg-accent active:scale-95 after:absolute after:-inset-2 after:content-['']"
                    >
                        <x-lucide-chevron-left class="size-5 rotate-180" defer/>
                    </button>
                </div>
            </section>
        @endif

        <section class="space-y-6 mt-12">
            @if($discover->isNotEmpty())
                <h2 class="text-2xl font-bold tracking-tight text-center">{{ __('index.clips.submitted') }}</h2>
                <div x-data="clipsInfiniteLoader()">
                    <div x-ref="clipsContainer"
                         class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3 md:gap-4 xl:gap-6">
                        <x-clips.preview-list :clips="$discover"/>
                    </div>

                    @if($discover->hasMorePages())
                        <div x-show="hasMore && !isLoading" x-intersect.margin.500px="loadMore"></div>

                        <x-index.discover.loading/>
                        <x-index.discover.error-loading-clips/>
                    @endif

                    <x-ui.template x-if="!hasMore" :if="$discover->hasMorePages()">
                        <x-index.discover.no-more-clips/>
                    </x-ui.template>

                    <noscript>
                        <div class="mt-6 border-t pt-6">
                            {{ $discover->links() }}
                        </div>
                    </noscript>
                </div>
            @else
                <x-index.discover.no-clips-found/>
            @endif
        </section>
    </div>


    @push('elements')
        <script>
            const DEBOUNCE_MS = 1000;

            document.addEventListener('alpine:init', () => {
                Alpine.data('clipsInfiniteLoader', () => ({
                    nextCursor: '{{ $discover->nextPageUrl() }}',
                    isLoading: false,
                    isError: false,
                    hasMore: {{ $discover->hasMorePages() ? 'true' : 'false' }},
                    _debounceTimeout: null,

                    get loading() {
                        return this.isLoading && !this.isError;
                    },

                    loadMore() {
                        if (this.isLoading || !this.hasMore || !this.nextCursor || this._debounceTimeout) return;

                        this.isLoading = true;

                        this._debounceTimeout = setTimeout(() => {
                            window.axios.get(this.nextCursor)
                                .then(response => {
                                    const html = response.data;
                                    this.nextCursor = response.headers['x-next-page'];
                                    requestAnimationFrame(() => {
                                        this.$refs.clipsContainer.insertAdjacentHTML('beforeend', html);
                                        this.hasMore = !!this.nextCursor;
                                        this.isError = false;
                                    });
                                })
                                .catch(error => {
                                    console.error('Error loading clips: ', error);
                                    this.isError = true;
                                })
                                .finally(() => {
                                    this.isLoading = false;
                                    this._debounceTimeout = null;
                                });
                        }, DEBOUNCE_MS);
                    }
                }));
            });
        </script>
    @endpush
</x-layout>
