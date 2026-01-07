@use(\Filament\Forms\Components\Field)
<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div
        x-data="{
            state: @if($entry instanceof Field) $wire.entangle('{{ $entry->getStatePath() }}') @else @js($getState()) @endif,
            isLoading: true,
            init() {
                this.$watch('state', () => this.isLoading = true)
            }
        }"
        class="relative w-full overflow-hidden rounded bg-gray-100 dark:bg-gray-800/50 aspect-video"
        {{ $getExtraAttributeBag() }}
    >
        {{-- Empty State --}}
        <div
            x-show="!state"
            x-cloak
            class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-gray-400 dark:text-gray-500"
        >
            <span class="text-sm font-medium">Wo Clip?</span>
        </div>

        {{-- Content State --}}
        <template x-if="state">
            <div class="absolute inset-0 h-full w-full bg-gray-900">
                {{-- Anti Flashbang(TM) Overlay --}}
                <div
                    x-show="isLoading"
                    x-transition:leave="transition ease-out duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-gray-100 text-gray-400 dark:bg-gray-800/50 dark:text-gray-500"
                >
                    <span class="text-xs font-medium tracking-wider opacity-75">Lade Clip...</span>
                </div>

                <iframe
                    :src="`https://clips.twitch.tv/embed?clip=${state}&parent={{ request()->getHost() }}`"
                    class="absolute inset-0 h-full w-full transition-opacity duration-700 ease-out"
                    :class="isLoading ? 'opacity-0' : 'opacity-100'"
                    @load="isLoading = false"
                    allow="fullscreen"
                >
                </iframe>
            </div>
        </template>
    </div>
</x-dynamic-component>
