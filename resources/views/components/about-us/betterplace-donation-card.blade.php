<section class="w-full">
    <div class="mx-auto grid max-w-7xl grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:gap-12 mb-8">
        <x-ui.card
            class="flex w-full flex-col rounded-2xl border border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 p-6 shadow-xl ring-1 shadow-black/10 ring-black/5 md:p-8 lg:h-220 dark:border-white/20 dark:bg-black/30 dark:bg-none! dark:ring-0 dark:shadow-purple-900/30">
            <div class="custom-scrollbar overflow-y-auto pr-2">
                @if ($error)
                    <div
                        class="rounded-xl border border-red-300/80 bg-red-50/60 p-4 dark:border-red-900/30 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200">
                            {{ $error }}
                        </p>
                    </div>
                @else
                    <div class="space-y-6">
                        <h2
                            class="bg-linear-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-2xl font-bold text-transparent sm:text-3xl dark:from-purple-300 dark:via-white dark:to-cyan-300">
                            {{ $projectTitle }}
                        </h2>

                        @if ($projectDescription)
                            <div class="text-sm md:text-base prose dark:prose-invert">
                                {!! $projectDescription !!}
                            </div>
                        @endif

                        <div
                            class="rounded-xl border border-gray-300/80 bg-white/60 p-6 dark:border-white/15 dark:bg-black/20">
                            <div
                                class="mb-4 flex items-center justify-center gap-2 text-xs font-medium tracking-wider uppercase sm:text-sm">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                <span class="text-gray-800 dark:text-white/90">
                                    {{ __('betterplace.total') }}
                                </span>
                                <span class="size-1.5 rounded-full bg-cyan-500"></span>
                            </div>
                            <div
                                class="bg-linear-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-center text-3xl font-bold text-transparent sm:text-4xl md:text-5xl dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                {{ $projectAmount }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card
            class="flex w-full flex-col rounded-2xl border border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 p-6 shadow-xl ring-1 shadow-black/10 ring-black/5 md:p-8 lg:h-220 dark:border-white/20 dark:bg-black/30 dark:bg-none! dark:ring-0 dark:shadow-purple-900/30">
            <div class="flex h-full flex-col">
                <h3
                    class="mb-6 bg-linear-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-xl font-bold text-transparent sm:text-2xl dark:from-purple-300 dark:via-white dark:to-cyan-300">
                    {{ __('betterplace.last_donations') }}
                </h3>

                @if ($error)
                    <div
                        class="rounded-xl border border-red-300/80 bg-red-50/60 p-4 dark:border-red-900/30 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200">
                            {{ $error }}
                        </p>
                    </div>
                @elseif($donations === [])
                    <div class="flex flex-1 items-center justify-center">
                        <p class="text-gray-800 dark:text-white/90">
                            {{ __('betterplace.no_donations_yet') }}
                        </p>
                    </div>
                @else
                    <div class="flex-1 overflow-hidden">
                        <style>
                            .custom-scrollbar::-webkit-scrollbar {
                                width: 6px;
                            }

                            .custom-scrollbar::-webkit-scrollbar-track {
                                background: rgba(0, 0, 0, 0.05);
                                border-radius: 10px;
                            }

                            .custom-scrollbar::-webkit-scrollbar-thumb {
                                background: rgba(0, 0, 0, 0.2);
                                border-radius: 10px;
                            }

                            .dark .custom-scrollbar::-webkit-scrollbar-track {
                                background: rgba(255, 255, 255, 0.05);
                            }

                            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                                background: rgba(255, 255, 255, 0.2);
                            }
                        </style>

                        <div class="custom-scrollbar h-full max-h-120 overflow-y-auto pr-2 lg:max-h-none">
                            @foreach ($donations as $donation)
                                <div class="border-b border-gray-300/80 py-4 last:border-b-0 dark:border-white/15">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-20 shrink-0 bg-linear-to-r from-purple-700 to-cyan-700 bg-clip-text text-base font-bold text-transparent dark:from-purple-300 dark:to-cyan-300">
                                            {{ Number::currency($donation['amount'], 'EUR', app()->getLocale(), 2) }}
                                        </div>
                                        {{--
                                            <div class="shrink-0">
                                                @if ($donation['image'])
                                                    <img src="{{ $donation['image'] }}" alt="{{ $donation['name'] }}"
                                                        class="size-9 rounded-full border border-gray-300/80 object-cover dark:border-white/20" />
                                                @else
                                                    <div class="size-9 rounded-full bg-emerald-400/80"></div>
                                                @endif
                                            </div>
                                        --}}

                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="truncate text-sm font-semibold text-gray-900 dark:text-white/90">
                                                {{ $donation['name'] }}
                                            </div>
                                            @if ($donation['message'])
                                                <div class="mt-1 line-clamp-3 text-xs wrap-break-word text-[#8ea0ff]">
                                                    {{ $donation['message'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="border-t border-gray-300/80 pt-4 sm:pt-6 dark:border-white/15">
                    <div class="flex justify-center">
                        <a href="https://secure.betterplace.org/de/donate/platform/fundraising-events/{{ $eventId }}"
                            target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto">
                            <button type="button"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:bg-primary/90 h-10 has-[>svg]:px-4 w-full rounded-full border-0 bg-linear-to-r from-emerald-500 via-teal-400 to-cyan-400 px-6 py-4 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-emerald-600 hover:via-teal-500 hover:to-cyan-500 hover:shadow-xl hover:shadow-emerald-500/25 sm:px-8 sm:py-5 sm:text-base">
                                <x-lucide-heart class="inline-block mr-2 size-4 text-white" />
                                {{ __('betterplace.donate_now') }}
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</section>
