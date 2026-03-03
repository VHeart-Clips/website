import { AlpineComponent } from 'alpinejs';
import baseEmbed, { GenericEmbedConfig, GenericEmbedData } from './base-embed';

export type TwitchClipId = string;

export interface TwitchEmbedConfig extends GenericEmbedConfig {
    clip?: string;
}

export interface TwitchEmbedData extends GenericEmbedData {
    clipId: string;
    updateUrl(id: TwitchClipId): void;
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

        updateUrl(id) {
            if (id) {
                const hostname = window.location.hostname;
                this.url = `https://clips.twitch.tv/embed?clip=${id}&parent=${hostname}`;
            }
        },

        init() {
            console.debug('Twitch Embed Init', { clipId: this.clipId });

            if (base.init) {
                base.init.call(this);
            }

            this.$watch('clipId', (value) => {
                this.updateUrl(value);
            });

            this.updateUrl(this.clipId);
        },
    };
};
