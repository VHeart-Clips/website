@props(['clip' => ''])

<div
    x-data="twitchEmbed({ clip: '{{ $clip }}' })"
    x-modelable="clipId"
    {{ $attributes }}
>
    <x-embeds.base>
        <x-slot:prompt>
            <x-embeds.prompt.shell>
                <p class="text-base font-medium text-balance text-zinc-400">
                    {{ __('embeds.twitch.consent.text') }}
                </p>

                <x-embeds.prompt.consent-button class="bg-purple-600 text-white hover:bg-purple-500 hover:text-white">
                    {{ __('embeds.twitch.consent.button') }}
                </x-embeds.prompt.consent-button>

                <a
                    href="https://www.twitch.tv/p/legal/privacy-notice/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-md text-zinc-500 underline hover:text-zinc-300"
                >
                    {{ __('embeds.twitch.consent.privacy-policy') }}
                </a>
            </x-embeds.prompt.shell>
        </x-slot:prompt>
    </x-embeds.base>
</div>
