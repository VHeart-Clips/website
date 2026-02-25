import Alpine, { AlpineComponent } from 'alpinejs';
import baseEmbed, { GenericEmbedConfig, GenericEmbedData } from './base-embed';

export interface TwitchEmbedConfig extends GenericEmbedConfig {
    clip?: string;
}

export interface TwitchEmbedData extends GenericEmbedData {
    clipId: string;
}

export default (
    config: TwitchEmbedConfig,
): AlpineComponent<TwitchEmbedData> => {
    const base = baseEmbed({
        ...config,
        url: '',
        cookieName: 'twitch_embed_consent',
        title: config.title || 'Twitch Clip',
    });

    return {
        ...base,
        clipId: config.clip || '',

        init() {
            console.debug('Twitch Embed Init', { clipId: this.clipId });

            if (base.init) {
                base.init.call(this);
            }

            Alpine.effect(() => {
                if (this.clipId) {
                    const hostname = window.location.hostname;
                    this.url = `https://clips.twitch.tv/embed?clip=${this.clipId}&parent=${hostname}`;
                }
            });
        },
    };
};
