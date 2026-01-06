<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
<div
    class="w-full overflow-hidden rounded-xl shadow-lg"
    style="aspect-ratio: 16/9;"
    {{ $getExtraAttributeBag() }}
>
    <iframe
        src="https://clips.twitch.tv/embed?clip={{ $getState() }}&parent={{ request()->getHost() }}"
        height="100%"
        width="100%"
        allow="fullscreen">
    </iframe>
</div>

</x-dynamic-component>
