<section class="w-full">
    <div class="mx-auto grid max-w-7xl grid-cols-1 items-start">
        <x-ui.card class="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-8 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
            <div class="mb-8">
                <div class="mb-10 text-center">
                    <div class="mb-4 flex items-center justify-center gap-3">
                        <x-lucide-video class="h-6 w-6 text-gray-900/90 dark:text-white"></x-lucide-video>
                        <h2 class="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-3xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                            {{ __('about.clip_process.title') }}
                        </h2>
                    </div>
                    <p class="mx-auto max-w-3xl text-base leading-relaxed text-gray-800 dark:text-white/90">
                        {{ __('about.clip_process.intro') }}
                    </p>
                </div>

                <div class="mb-8 grid gap-5 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-300/80 bg-white/65 p-6 transition-transform duration-200 hover:scale-[1.02] dark:border-white/10 dark:bg-black/25">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                <x-lucide-vote class="h-5 w-5 text-gray-900/90 dark:text-white"></x-lucide-vote>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900/90 dark:text-white/90">
                                {{ __('about.clip_process.steps.community.title') }}
                            </h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                            {{ __('about.clip_process.steps.community.description') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-300/80 bg-white/65 p-6 transition-transform duration-200 hover:scale-[1.02] dark:border-white/10 dark:bg-black/25">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                <x-lucide-users class="h-5 w-5 text-gray-900/90 dark:text-white"></x-lucide-users>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900/90 dark:text-white/90">
                                {{ __('about.clip_process.steps.jury.title') }}
                            </h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                            {{ __('about.clip_process.steps.jury.description') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-300/80 bg-white/65 p-6 transition-transform duration-200 hover:scale-[1.02] dark:border-white/10 dark:bg-black/25">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                <x-lucide-shield-check class="h-5 w-5 text-gray-900/90 dark:text-white"></x-lucide-shield-check>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900/90 dark:text-white/90">
                                {{ __('about.clip_process.steps.moderation.title') }}
                            </h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                            {{ __('about.clip_process.steps.moderation.description') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-300/80 bg-white/65 p-6 transition-transform duration-200 hover:scale-[1.02] dark:border-white/10 dark:bg-black/25">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                <x-lucide-video class="h-5 w-5 text-gray-900/90 dark:text-white"></x-lucide-video>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900/90 dark:text-white/90">
                                {{ __('about.clip_process.steps.edit.title') }}
                            </h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                            {{ __('about.clip_process.steps.edit.description') }}
                        </p>
                    </div>
                </div>

                <div class="mb-8 rounded-xl border border-gray-300/80 bg-white/65 p-6 dark:border-white/10 dark:bg-black/25">
                    <p class="text-base leading-relaxed text-gray-800 dark:text-white/90">
                        {{ __('about.clip_process.neutrality') }}
                    </p>
                </div>

                <div class="rounded-xl border border-red-300 bg-red-50/80 p-6 dark:border-red-400/30 dark:bg-red-900/10">
                    <div class="flex items-start gap-3">
                        <x-lucide-shield class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-300"></x-lucide-shield>
                        <p class="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                            {{ __('about.clip_process.blacklist') }}
                        </p>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</section>
