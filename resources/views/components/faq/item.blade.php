@props(['id' => null, 'title' => 'Title'])
<details
    id="{{ $id }}"
    name="faq-accordion"
    class="group rounded-xl border border-gray-400 bg-white/80 shadow-2xl ring-1 shadow-black/10 ring-black/5 transition-all duration-200 dark:border-gray-600 dark:bg-black/30 dark:ring-0 dark:shadow-purple-900/30"
>
    <summary
        class="flex cursor-pointer list-none items-center gap-3 px-5 py-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent rounded-xl select-none"
    >
        <x-lucide-circle-question-mark defer class="size-6 font-bold text-accent shrink-0" aria-hidden="true" />
        <span class="flex-1 text-base font-medium">{{ $title }}</span>
        <x-lucide-chevron-down defer class="size-6 transform transition-transform duration-200 group-open:rotate-180 group-open:text-accent shrink-0" aria-hidden="true" />
    </summary>

    <div class="rounded-b-xl border-t border-gray-200 bg-white/50 px-5 py-4 dark:border-white/20 dark:bg-white/5 flex gap-3">
        <span class="font-bold text-emerald-600 dark:text-emerald-400 hidden md:inline" aria-hidden="true">
            A
        </span>
        <div class="prose dark:prose-invert text-balance max-w-none">
            {{ $slot }}
        </div>
    </div>
</details>
