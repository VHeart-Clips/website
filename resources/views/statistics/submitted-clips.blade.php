<x-layout
class="max-w-5xl w-full mx-auto space-y-6"
:title="__('user.statistics_details.title',['name' => __('user.statistics_details.submitted-clips.name')])"
>

    <div class="m-auto py-8">
        <x-ui.card variant="glass">
            <x-ui.card.header class="pb-6 border-b border-border">
                <x-ui.card.title class="text-center text-2xl font-bold tracking-tight">
                    <h1>{{ __('user.statistics_details.submitted-clips.heading') }}</h1>
                </x-ui.card.title>
            </x-ui.card.header>

            <x-ui.card.content class="p-4 pt-6 space-y-8">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-foreground">
                        {{ __('user.statistics_details.submitted-clips.subheading') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ __('user.statistics_details.submitted-clips.description') }}
                    </p>
                </div>
            </x-ui.card.content>
        </x-ui.card>
    </div>

    <section>
        @if($submittedClips->isNotEmpty())
            <div x-data="submittedClipsInfiniteLoader()">
                <div x-ref="submittedClipsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-statistics.submitted-clips-list :submittedClips="$submittedClips"/>
                </div>

                @if($submittedClips->hasMorePages())
                    <div x-show="hasMore && !isLoading" x-intersect.margin.500px="loadMore"></div>

                    <x-statistics.infinite-loader.loading/>
                    <x-statistics.infinite-loader.error-loading :name="__('user.statistics_details.submitted-clips.name')"/>
                @endif

                <x-ui.template x-if="!hasMore" :if="$submittedClips->hasMorePages()">
                    <x-statistics.infinite-loader.no-more :name="__('user.statistics_details.submitted-clips.name')"/>
                </x-ui.template>

            </div>
        @else
            <x-statistics.infinite-loader.nothing-found :name="__('user.statistics_details.submitted-clips.name')"/>
        @endif
    </section>

    @push('elements')
    <script>
        const DEBOUNCE_MS = 1000;

        document.addEventListener('alpine:init', () => {
            Alpine.data('submittedClipsInfiniteLoader', () => ({
                    nextCursor: '{{ $submittedClips->nextPageUrl() }}',
                    isLoading: false,
                    isError: false,
                    hasMore: {{ $submittedClips->hasMorePages() ? 'true' : 'false' }},
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
                                        this.$refs.submittedClipsContainer.insertAdjacentHTML('beforeend', html);
                                        this.hasMore = !!this.nextCursor;
                                        this.isError = false;
                                    });
                                })
                                .catch(error => {
                                    console.error('Error loading votes: ', error);
                                    this.isError = true;
                                })
                                .finally(() => {
                                    this.isLoading = false;
                                    this._debounceTimeout = null;
                                });
                        }, DEBOUNCE_MS);
                    }
            }))
        })
    </script>
    @endpush


</x-layout>
