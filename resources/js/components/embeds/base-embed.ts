import CookieManager from '@/lib/cookieManager';
import { checkInView } from '@/lib/utils';
import { AlpineComponent } from 'alpinejs';

const ViewportBuffer = 100;

export interface GenericEmbedConfig {
    url?: string;
    link?: string;
    cookieName?: string;
    title?: string;
    thumbnailUrl?: string;
}

export interface GenericEmbedData {
    url: string | null;
    link: string | null;
    title: string;
    isLoading: boolean;
    hasConsentGiven: boolean;
    isValidUrl: boolean;
    thumbnailUrl: string | null;
    isVisible: boolean;
    hasConsent(): boolean;
    accept(): void;
    handleIframeLoad(): void;
    checkIframeLoaded(): void;
    init(): void;
    setVisible(): void;
    cookieManager: CookieManager | null;
}

export default (
    config: GenericEmbedConfig,
): AlpineComponent<GenericEmbedData> => ({
    url: config.url || null,
    link: config.link || null,
    title: config.title || 'Embed',
    thumbnailUrl: config.thumbnailUrl || null,
    isLoading: true,
    hasConsentGiven: false,
    isValidUrl: true,
    isVisible: false,
    cookieManager: config.cookieName
        ? new CookieManager(config.cookieName)
        : null,

    init() {
        const el = this.$el as HTMLImageElement;

        if (checkInView(el, ViewportBuffer)) {
            this.isVisible = true;
        }

        this.$watch('url', () => {
            this.isLoading = true;

            if (!this.url || this.url.length === 0) {
                this.isValidUrl = false;
                return;
            }

            try {
                new URL(this.url);
                this.isValidUrl = true;

                // eslint-disable-next-line @typescript-eslint/no-unused-vars
            } catch (error) {
                this.isValidUrl = false;
            }
        });

        this.checkIframeLoaded();
        this.cookieManager?.consentManager.subscribe(() => {
            if (this.cookieManager && this.cookieManager.hasConsent === false) {
                this.hasConsentGiven = false;
                this.cookieManager?.remove();
            }
        });
    },

    hasConsent() {
        if (!this.cookieManager) {
            return this.hasConsentGiven;
        }

        return (
            this.hasConsentGiven ||
            (this.cookieManager?.hasConsent && !!this.cookieManager?.get())
        );
    },

    accept() {
        this.hasConsentGiven = true;

        this.cookieManager?.set('1', {
            days: 30,
        });
    },
    setVisible() {
        this.isVisible = true;
    },
    handleIframeLoad() {
        this.isLoading = false;
    },

    /**
     * because of a (maybe possible) edge case with very fast load times we will just check every frame
     * for a few ms if the iframe has been loaded outside of alpines lifecycle somehow
     * if we cannot load within the 250ms time frame we should be able to assume that
     * the @load event will actually fire anyway in the future, this is really just
     * for the edgcase where it would be too fast lol
     */
    checkIframeLoaded() {
        const start = Date.now();
        let iframe: HTMLIFrameElement;

        const poll = () => {
            // $ref is not reliable in this case from what i tried
            const found = this.$el.querySelector('iframe') as HTMLIFrameElement;
            if (found) iframe = found;

            if (iframe?.dataset.loaded) {
                this.handleIframeLoad();
                return;
            }

            if (this.isLoading && Date.now() - start < 250) {
                requestAnimationFrame(poll);
            }
        };
        poll();
    },
});
