@props(['yid' => null, 'url' => null, 'autoplay' => false])

<div
    x-data="youtubeEmbed({
        youtubeId: {{ $yid ? "'{$yid}'" : "null" }},
        youtubeUrl: {{ $url ? "'{$url}'" : "null" }},
        autoplay: {{ $autoplay ? 'true' : 'false' }}
    })"
    x-modelable="[youtubeId, youtubeUrl, autoplay]"
    {{ $attributes }}
>
    <x-embeds.base>
        <x-slot:prompt>
            <x-embeds.prompt.shell>
                <p class="md:font-medium text-balance text-zinc-400">
                    {{ __('embeds.youtube.consent.text') }}
                </p>

                <x-embeds.prompt.consent-button class="bg-red-600 text-white hover:bg-red-500 hover:text-white">
                    {{ __('embeds.youtube.consent.button') }}
                </x-embeds.prompt.consent-button>

                <a
                    href="https://www.youtube.com/howyoutubeworks/privacy/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-zinc-500 underline hover:text-zinc-300"
                >
                    {{ __('embeds.youtube.consent.privacy-policy') }}
                </a>
            </x-embeds.prompt.shell>
        </x-slot:prompt>
    </x-embeds.base>
</div>
