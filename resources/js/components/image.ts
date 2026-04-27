import CookieManager from '@/lib/cookieManager';
import { AlpineComponent } from 'alpinejs';

export type ImageStatus = 'loading' | 'loaded' | 'error';

export interface ImageConfig {
    src: string;
    alt: string;
    cookieName?: string;
    initialConsent?: boolean;
}

export interface ImageData {
    src: string;
    alt: string;
    shown: boolean;
    imageStatus: ImageStatus;
    isCached: boolean;
    hasConsentGiven: boolean;
    imageBindings: Record<string, unknown>;
    cookieManager: CookieManager | null;
    checkCached(el: HTMLImageElement): void;
    show(): void;
    hasConsent(): boolean;
    init(): void;
}

export default (config: ImageConfig): AlpineComponent<ImageData> => ({
    src: config.src,
    alt: config.alt,
    shown: false,
    imageStatus: 'loading',
    isCached: false,
    hasConsentGiven: config.initialConsent ?? !config.cookieName,
    cookieManager: config.cookieName
        ? new CookieManager(config.cookieName)
        : null,

    init() {
        if (!this.cookieManager || this.hasConsentGiven) return;

        const unsubscribe = this.cookieManager.consentManager.subscribe(() => {
            if (!this.cookieManager) return;

            if (this.cookieManager.hasConsent) {
                this.hasConsentGiven = true;
            } else {
                this.hasConsentGiven = false;
                this.imageStatus = 'loading';
                this.cookieManager.remove();
            }
        });

        const tryUnsub = () => {
            if (
                this.shown &&
                (this.imageStatus === 'loaded' || this.imageStatus === 'error')
            )
                unsubscribe();
        };

        this.$watch('shown', tryUnsub);
        this.$watch('imageStatus', tryUnsub);
    },

    hasConsent() {
        if (!this.cookieManager) return true;
        return (
            this.hasConsentGiven ||
            (this.cookieManager.hasConsent && !!this.cookieManager.get())
        );
    },

    checkCached(el: HTMLImageElement) {
        if (el.complete && el.naturalWidth > 0) {
            this.isCached = true;
        }
    },

    show() {
        this.shown = true;
    },

    imageBindings: {
        [':src']() {
            return this.src;
        },
        [':alt']() {
            return this.alt;
        },
        ['@load']() {
            this.imageStatus = 'loaded';
        },
        ['@error']() {
            this.imageStatus = 'error';
        },
        [':data-status']() {
            return this.imageStatus;
        },
        [':data-cached']() {
            return this.isCached ? 'true' : 'false';
        },
        ['loading']: 'lazy',
        ['decoding']: 'async',
    },
});
