<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-load
        x-cloak
        x-data="customFileUpload({
            state: $wire.$entangle(@js($getStatePath(), JSON_THROW_ON_ERROR)),
            ...@js([
                'presignUrl' => route('clip.presign'),
                'configToken' => $getUploadConfigToken(),
            ], JSON_THROW_ON_ERROR),
        })"
        class="fi-fo-file-upload space-y-2 transition-transform"
    >
        <input
            x-ref="input"
            type="file"
            class="hidden"
            accept="{{ implode(',', array_keys($getAcceptedFileTypes())) }}"
            x-on:change="upload($event.target.files[0]); $event.target.value = ''"
        />

        <button
            type="button"
            x-show="! state && ! uploading"
            x-on:click="$refs.input.click()"
            x-on:dragover.prevent="$el.dataset.dragging = ''"
            x-on:dragleave="delete $el.dataset.dragging"
            x-on:drop.prevent="delete $el.dataset.dragging; upload($event.dataTransfer.files[0])"
            class="flex w-full flex-col items-center gap-y-1 *:pointer-events-none rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-sm text-gray-600 transition hover:border-primary-500 hover:bg-primary-50 data-dragging:border-primary-500 data-dragging:bg-primary-50 dark:border-white/20 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:data-dragging:bg-white/10"
        >
            <x-filament::icon icon="lucide-upload" class="size-6 text-gray-400 dark:text-gray-500"/>
            <span>
                <span class="font-medium text-primary-600 dark:text-primary-400">Choose a file</span>
                or drag it here
            </span>
        </button>

        <div
            x-show="state && ! uploading"
            class="flex items-center gap-x-3 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        >
            <x-filament::icon icon="lucide-film" class="size-5 shrink-0 text-gray-400 dark:text-gray-500"/>

            <span class="min-w-0 flex-1 truncate text-sm text-gray-950 dark:text-white" x-text="fileName"></span>

            <x-filament::badge color="warning" size="sm" x-show="isTemporary">
                Unsaved
            </x-filament::badge>

            <x-filament::icon-button
                icon="lucide-refresh-cw"
                color="gray"
                label="Replace"
                tooltip="Replace"
                x-on:click="$refs.input.click()"
            />

            <x-filament::icon-button
                icon="lucide-trash"
                color="danger"
                label="Remove"
                tooltip="Remove"
                x-on:click="remove()"
            />
        </div>

        <div
            x-show="uploading"
            x-bind:data-yura="yuraVisible"
            class="group isolate relative flex items-center gap-x-3 overflow-hidden rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        >
            <img
                src="{{ Vite::asset('resources/images/webp/memes/YuraWaiting2.webp') }}"
                alt=""
                loading="lazy"
                decoding="async"
                aria-hidden="true"
                class="pointer-events-none absolute inset-y-0 right-[4.5ch] -z-10 h-full object-cover object-right opacity-0 transition-opacity duration-10000 ease-linear mask-[linear-gradient(to_right,transparent,black)] group-data-yura:opacity-40"
            />

            <x-filament::loading-indicator class="size-5 shrink-0 text-primary-600 dark:text-primary-400"/>

            <span
                x-show="retries > 0"
                class="shrink-0 text-xs text-warning-600 dark:text-warning-400"
                x-text="`Retry ${retries}/${maxRetries}`"
            ></span>

            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                <div class="h-full bg-primary-600 transition-[width]" x-bind:style="{ width: progress + '%' }"></div>
            </div>

            <span
                class="hidden min-w-[11ch] shrink-0 text-xs tabular-nums text-gray-500 sm:inline dark:text-gray-400"
                x-text="stats"
            ></span>

            <span
                class="w-[5ch] shrink-0 text-end text-xs tabular-nums text-gray-500 dark:text-gray-400"
                x-text="progress + '%'"
            ></span>
        </div>

        <p x-show="error" x-text="error" role="alert" class="text-sm text-danger-600 dark:text-danger-400"></p>
    </div>
</x-dynamic-component>
