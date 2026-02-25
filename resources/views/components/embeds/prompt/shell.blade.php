<div class="absolute inset-0 z-20 h-full w-full bg-black">
    <div {{ $attributes->twMerge('flex h-full flex-col items-center justify-center space-y-4 p-6 text-center text-white') }}>
        {{ $slot }}
    </div>
</div>
