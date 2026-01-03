<x-layout :title="__('privacy.title')">
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
           aria-label="{{ __('privacy.back_button') }}">
            ← {{ __('privacy.back_button') }}
        </a>

        <div class="w-full max-w-4xl mx-auto px-6 py-20 relative z-10">
            <header class="text-center mb-8 p-7 rounded-2xl
                      border border-gray-200/60 dark:border-white/18
                      bg-white/70 dark:bg-black/35 backdrop-blur-lg
                      shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h1 class="mb-2.5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ __('privacy.header.title') }}
                </h1>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-left text-base">
                    {{ __('privacy.header.intro') }}
                </p>
            </header>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('privacy.responsible.title') }}
                </h2>
                <div class="text-gray-700 dark:text-white/85 space-y-2 text-base">
                    {!! __('privacy.responsible.content') !!}
                    <p><strong>{{ __('privacy.responsible.email') }}</strong>
                        <a href="mailto:{{ __('privacy.responsible.mail') }}"
                           class="text-blue-600 dark:text-white/90 underline underline-offset-3 opacity-90">
                            {{ __('privacy.responsible.mail') }}
                        </a>
                    </p>
                    <p>
                        <strong>{{ __('privacy.responsible.phone') }}</strong>
                        <a href="tel:{{ __('privacy.responsible.number') }}"
                           class="text-blue-600 dark:text-white/90 underline underline-offset-3 opacity-90">
                            {{ __('privacy.responsible.number') }}
                        </a>
                    </p>
                </div>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('privacy.technical.title') }}
                </h2>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base">
                    {{ __('privacy.technical.content') }}
                </p>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('privacy.third_party.title') }}
                </h2>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base">
                    {{ __('privacy.third_party.content') }}
                </p>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white opacity-95">
                    {{ __('privacy.google_fonts.title') }}
                </h3>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base mb-3">
                    {{ __('privacy.google_fonts.content') }}
                </p>

                <a href="https://policies.google.com/privacy"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-full
          border border-purple-300/35 dark:border-purple-300/35
          bg-gradient-to-r from-purple-500/15 to-blue-400/10
          dark:from-purple-500/22 dark:to-blue-500/14
          text-gray-800 dark:text-white font-semibold text-sm
          no-underline backdrop-blur-sm mt-2">
                    {{ __('privacy.google_fonts.link') }}
                </a>

            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white opacity-95">
                    {{ __('privacy.discord.title') }}
                </h3>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base mb-3">
                    {{ __('privacy.discord.content') }}
                </p>

                <div class="space-y-2">
                    <a href="https://policies.google.com/privacy"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-full
          border border-purple-300/35 dark:border-purple-300/35
          bg-gradient-to-r from-purple-500/15 to-blue-400/10
          dark:from-purple-500/22 dark:to-blue-500/14
          text-gray-800 dark:text-white font-semibold text-sm
          no-underline backdrop-blur-sm mt-2">
                        {{ __('privacy.google_fonts.link') }}
                    </a>

                </div>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white opacity-95">
                    {{ __('privacy.youtube.title') }}
                </h3>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base mb-3">
                    {{ __('privacy.youtube.content') }}
                </p>

                <div class="space-y-2">
                    <a href="https://policies.google.com/privacy"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-full
              border border-purple-300/35 dark:border-purple-300/35
              bg-gradient-to-r from-purple-500/15 to-blue-400/10
              dark:from-purple-500/22 dark:to-blue-500/14
              text-gray-800 dark:text-white font-semibold text-sm
              no-underline backdrop-blur-sm">
                        {{ __('privacy.youtube.privacy_link') }}
                    </a>
                </div>
            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white opacity-95">
                    {{ __('privacy.cataas.title') }}
                </h3>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base mb-3">
                    {{ __('privacy.cataas.content') }}
                </p>

                <a href="https://cataas.com"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-full
          border border-purple-300/35 dark:border-purple-300/35
          bg-gradient-to-r from-purple-500/15 to-blue-400/10
          dark:from-purple-500/22 dark:to-blue-500/14
          text-gray-800 dark:text-white font-semibold text-sm
          no-underline backdrop-blur-sm mt-2">
                    {{ __('privacy.cataas.link') }}
                </a>

            </section>

            <section class="mb-6 p-7 rounded-2xl
                       border border-gray-200/60 dark:border-white/18
                       bg-white/70 dark:bg-black/35 backdrop-blur-lg
                       shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('privacy.rights.title') }}
                </h2>
                <p class="text-gray-700 dark:text-white/85 leading-relaxed text-base">
                    {{ __('privacy.rights.content') }}
                </p>
            </section>

            <footer class="mt-12 p-7 rounded-2xl
                      border border-gray-200/60 dark:border-white/18
                      bg-white/70 dark:bg-black/35 backdrop-blur-lg
                      shadow-lg dark:shadow-2xl shadow-gray-200/50 dark:shadow-black/45">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <p class="text-gray-600 dark:text-white/75 text-sm">
                        {{ __('privacy.footer.copyright') }}
                    </p>
                    <nav class="flex gap-4" aria-label="{{ __('Footer Navigation') }}">
                        <a class="text-gray-700 dark:text-white/85 text-sm no-underline"
                           href="/privacy">
                            {{ __('privacy.footer.privacy') }}
                        </a>
                        <a class="text-gray-700 dark:text-white/85 text-sm no-underline"
                           href="/imprint">
                            {{ __('privacy.footer.imprint') }}
                        </a>
                    </nav>
                </div>
            </footer>
        </div>
    </main>
</x-layout>
