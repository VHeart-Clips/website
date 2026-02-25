@props([
    'prompt',
])
<div
    x-intersect.margin.100px.once="setVisible"
    {{ $attributes->merge(['class' => "relative isolate overflow-hidden aspect-video rounded-lg bg-black dark:border-black"]) }}
>
    {{-- later we can add some fallback stuff so this can technically work with no javascript in a very basic way but for now this is enough --}}
    <noscript>
        <div class="absolute inset-0 z-20 h-full w-full bg-black">
            <div class="flex h-full flex-row items-center justify-center gap-4 p-6 text-center text-white">
                <x-lucide-info class="size-12 text-destructive" aria-hidden="true" defer />

                <p>{{ __('embeds.generic.noscript.text') }}</p>
            </div>
        </div>
    </noscript>

    <template x-if="!isValidUrl">
        <div class="absolute inset-0 z-20 h-full w-full bg-black">
            <div class="flex h-full flex-row items-center justify-center gap-4 p-6 text-center text-white">
                <x-lucide-info class="size-12 text-destructive" aria-hidden="true" defer />
                <p>{{ __('embeds.generic.invalid.text') }}</p>
            </div>
        </div>
    </template>

    <template x-if="isValidUrl && url">
        <div class="h-full w-full">
            <template x-if="!hasConsent()">
                @if($prompt?->hasActualContent())
                    {{ $prompt }}
                @else
                    <x-embeds.prompt.shell>
                        <p class="text-base font-medium text-balance text-zinc-400">
                            {{ __('embeds.generic.consent.text') }}
                        </p>

                        <x-embeds.prompt.consent-button>
                            {{ __('embeds.generic.consent.button') }}
                        </x-embeds.prompt.consent-button>

                        <template x-if="link" >
                            <a
                                :href="link"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-md text-zinc-500 underline hover:text-zinc-300"
                            >
                                {{ __('embeds.generic.consent.link-text') }}
                            </a>
                        </template>
                    </x-embeds.prompt.shell>
                @endif
            </template>

            <template x-if="hasConsent() && isVisible">
                <div class="h-full w-full relative">

                    <div x-show="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-black text-gray-500">
                        <x-lucide-loader-circle class="size-12 animate-spin opacity-75" aria-hidden="true" defer />
                    </div>

                    <iframe
                        :src="url"
                        :title="title"
                        @load="handleIframeLoad()"
                        class="h-full w-full border-0 transition-opacity duration-500"
                        :class="isLoading ? 'opacity-0' : 'opacity-100'"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        allowFullScreen
                        loading="lazy"
                    ></iframe>
                </div>
            </template>
        </div>
    </template>
</div>
