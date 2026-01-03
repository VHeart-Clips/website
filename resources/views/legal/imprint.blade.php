<x-layout :title="__('imprint.title')">
    <main class="min-h-screen relative overflow-x-hidden bg-gray-50 dark:bg-gray-900">
        <div class="fixed inset-0 dark:block hidden pointer-events-none">
            <div class="absolute inset-0"
                 style="background: radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.20) 0%, rgba(10,10,26,0) 45%),
                        radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.14) 0%, rgba(10,10,26,0) 50%),
                        #0a0a1a;">
            </div>
        </div>

        <a href="/" class="fixed top-5 left-5 z-50 inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-full
                      border border-purple-300/35 dark:border-purple-300/35
                      bg-gradient-to-r from-purple-500/15 to-blue-400/10 dark:from-purple-500/22 dark:to-blue-500/14
                      text-gray-800 dark:text-white font-semibold text-sm no-underline backdrop-blur-sm"
           aria-label="{{ __('imprint.back_button') }}">
            ← {{ __('imprint.back_button') }}
        </a>

        <div class="w-full max-w-4xl mx-auto px-6 py-20 relative z-10">
            <header class="text-center mb-8 p-7 rounded-2xl
                      border border-gray-200/60 dark:border-white/18
                      bg-white/70 dark:bg-black/35 backdrop-blur-lg
                      shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h1 class="mb-2.5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ __('imprint.header.title') }}
                </h1>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base">
                    {{ __('imprint.header.intro') }}
                </p>
            </header>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('imprint.provider.title') }}
                </h2>
                <div class="text-gray-700 dark:text-white/85 space-y-2 text-base">
                    {!! __('imprint.provider.content') !!}
                    <p><strong>{{ __('imprint.provider.email') }}</strong>
                        <a href="mailto:{{ __('imprint.provider.mail') }}"
                           class="text-blue-600 dark:text-white/90 underline underline-offset-3 opacity-90">
                            {{ __('imprint.provider.mail') }}
                        </a>
                    </p>
                    <p>
                        <strong>{{ __('imprint.provider.phone') }}</strong>
                        <a href="tel:{{ __('imprint.provider.number') }}"
                           class="text-blue-600 dark:text-white/90 underline underline-offset-3 opacity-90">
                            {{ __('imprint.provider.number') }}
                        </a>
                    </p>
                </div>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('imprint.liability_content.title') }}
                </h2>
                <div class="text-gray-700 dark:text-white/85 space-y-3 text-base">
                    <p>{{ __('imprint.liability_content.paragraph1') }}</p>
                    <p>{{ __('imprint.liability_content.paragraph2') }}</p>
                </div>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('imprint.liability_links.title') }}
                </h2>
                <div class="text-gray-700 dark:text-white/85 space-y-3 text-base">
                    <p>{{ __('imprint.liability_links.paragraph1') }}</p>
                    <p>{{ __('imprint.liability_links.paragraph2') }}</p>
                </div>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('imprint.copyright.title') }}
                </h2>
                <div class="text-gray-700 dark:text-white/85 space-y-3 text-base">
                    <p>{{ __('imprint.copyright.paragraph1') }}</p>
                    <p>{{ __('imprint.copyright.paragraph2') }}</p>
                </div>
            </section>

            <footer class="mt-12 p-7 rounded-2xl
                      border border-gray-200/60 dark:border-white/18
                      bg-white/70 dark:bg-black/35 backdrop-blur-lg
                      shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <p class="text-gray-600 dark:text-white/75 text-sm">
                        {{ __('imprint.footer.copyright') }}
                    </p>
                    <nav class="flex gap-4" aria-label="{{ __('Footer Navigation') }}">
                        <a class="text-gray-700 dark:text-white/85 text-sm no-underline"
                           href="/privacy">
                            {{ __('imprint.footer.privacy') }}
                        </a>
                        <a class="text-gray-700 dark:text-white/85 text-sm no-underline"
                           href="/imprint">
                            {{ __('imprint.footer.imprint') }}
                        </a>
                    </nav>
                </div>
            </footer>
        </div>
    </main>
</x-layout>
