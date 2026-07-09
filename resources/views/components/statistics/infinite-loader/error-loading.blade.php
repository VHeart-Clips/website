@props(['name' => 'Clips'])
<template x-if="isError">
    <div class="text-center py-12 space-y-2">
        <p class="font-medium h-8">{{ __('user.statistics_details.infinite-loader.loading-error',['name' => $name]) }}</p>
    </div>
</template>
