@props(['id' => 'modal', 'component' => 'modal'])
<div
    x-load
    x-data="{{ $component }}"
    x-on:{{ $id }}.window="openModal($event.detail)"
>
    <dialog
        x-ref="dialog"
        @cancel="handleCancel($event)"
        @click="if ($event.target === $el) closeModal()"
        aria-labelledby="modal-title-{{ $id }}"
        class="w-full max-w-full m-auto bg-transparent text-foreground border-none p-4 sm:p-0 backdrop:bg-background/80 backdrop:backdrop-blur-sm focus:outline-none"
    >
        <div
            x-show="modalOpen"
            x-transition:enter="ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            {{ $attributes->twMerge('relative w-full sm:max-w-lg mx-auto rounded-lg border bg-background text-foreground p-6 shadow-lg sm:rounded-lg overflow-hidden') }}
            @click.stop
        >
            {{ $slot }}
        </div>
    </dialog>
</div>
