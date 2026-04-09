<div
    x-load
    x-data="filamentClipOverlay(@js($initialState), '{{ $identifier }}')"
    x-init="init()"
    class="mt-4 space-y-4"
>
    <div class="flex items-center gap-3">
        <button
            type="button"
            x-on:click="downloadOverlay()"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-500"
        >
            <x-lucide-download class="size-4"/>
            1080p (x1)
        </button>
        <button
            type="button"
            x-on:click="downloadOverlay(2)"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-500"
        >
            <x-lucide-download class="size-4"/>
            4k (x2)
        </button>
        <button
            type="button"
            x-on:click="downloadOverlay(4)"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-500"
        >
            <x-lucide-download class="size-4"/>
            x4
        </button>
        <button
            type="button"
            x-on:click="downloadOverlay(8)"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-500"
        >
            <x-lucide-download class="size-4"/>
            x8
        </button>
    </div>

    <div
        id="clip-overlay-container"
        class="relative w-full overflow-hidden rounded-lg border border-gray-200 dark:border-white/10 aspect-video"
    >
        <img id="clip-overlay-preview" class="absolute inset-0 size-full object-contain" alt="Overlay preview"/>

        <div
            id="clip-overlay-loading"
            class="absolute isolate inset-0 flex items-center justify-center bg-white/50 dark:bg-black/30 backdrop-blur-sm"
        >
            <x-lucide-loader class="size-12 animate-spin text-primary-600"/>
        </div>
    </div>

    <div class="pointer-events-none fixed aspect-video"
         style="width:1920px;height:1080px;display: none;">
        <div
            id="clip-overlay-template"
            style="width:1920px;height:1080px;position:relative;background:transparent;"
        >
            <x-clips.overlay/>
        </div>
    </div>
</div>
