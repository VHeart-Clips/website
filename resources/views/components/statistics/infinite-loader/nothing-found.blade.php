@props(['name' => 'Clips'])
<div class="flex flex-col items-center justify-center">
    <x-lucide-video-off defer class="size-12 text-muted-foreground mb-4" />
    <h2 class="text-lg font-semibold">
        {{ __('user.statistics_details.infinite-loader.nothing-found',['name' => $name]) }}
    </h2>
    <p class="mt-2 text-sm text-muted-foreground text-center max-w-md text-balance">
        {{ __('user.statistics_details.infinite-loader.nothing-found-subtext',['name' => $name]) }}
    </p>
</div>
