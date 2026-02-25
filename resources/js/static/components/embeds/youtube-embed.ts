import Alpine, { AlpineComponent } from 'alpinejs';
import baseEmbed, { GenericEmbedConfig, GenericEmbedData } from './base-embed';

export interface YoutubeEmbedConfig extends GenericEmbedConfig {
    youtubeId?: string;
    youtubeUrl?: string;
    autoplay?: boolean;
}

export interface YoutubeEmbedData extends GenericEmbedData {
    youtubeId: string;
    youtubeUrl: string;
    autoplay: boolean;
}

export default (
    config: YoutubeEmbedConfig,
): AlpineComponent<YoutubeEmbedData> => {
    const base = baseEmbed({
        ...config,
        url: config.youtubeUrl || '',
        cookieName: 'youtube_embed_consent',
        title: config.title || 'Youtube Embed',
    });

    return {
        ...base,
        youtubeId: config.youtubeId || '',
        youtubeUrl: config.youtubeUrl || '',
        autoplay: config.autoplay || false,

        init() {
            if (base.init) {
                base.init.call(this);
            }

            Alpine.effect(() => {
                if (this.youtubeUrl) {
                    this.url = this.youtubeUrl;
                }

                if (this.youtubeId) {
                    const params = new URLSearchParams({
                        autoplay: this.autoplay ? '1' : '0',
                        rel: '0',
                        modestbranding: '1',
                    });

                    this.url = `https://www.youtube-nocookie.com/embed/${this.youtubeId}?${params.toString()}`;
                }
            });
        },
    };
};
