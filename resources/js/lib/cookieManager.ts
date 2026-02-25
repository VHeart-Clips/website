import CookieConsent, { cookieConsentManager } from '@/lib/cookieConsent.js';

// consent manager should be refactored into typescript later lol
export interface ConsentState {
    [key: string]: boolean;
}

/**
 * Get or Set a cookie.
 *
 * If we don't have consent it will ignore it though.
 */
export default class CookieManager {
    public readonly cookieName: string;
    public readonly consentManager: CookieConsent;

    constructor(cookieName: string) {
        this.cookieName = cookieName;
        this.consentManager = cookieConsentManager;
    }

    public get hasConsent(): boolean {
        const state = this.consentManager.getState() as ConsentState;
        return state[this.cookieName];
    }

    /**
     * Get the cookie.
     */
    public get(): string | null {
        if (typeof document === 'undefined' || !this.hasConsent) return null;

        const match = document.cookie.match(
            new RegExp(`(^|;\\s*)(${this.cookieName})=([^;]*)`),
        );

        return match && match[3] ? decodeURIComponent(match[3]) : null;
    }

    /**
     * Set the Cookie.
     */
    public set(
        value: string,
        options: {
            days?: number;
            path?: string;
            sameSite?: CookieSameSite;
            secure?: boolean;
        } = {},
    ): boolean {
        if (typeof document === 'undefined') return false;

        if (!this.hasConsent) {
            console.debug(
                `Cookie ${this.cookieName} shall not pass (missing consent)`,
            );
            return false;
        }

        const {
            days = 365,
            path = '/',
            sameSite = 'lax',
            secure = true,
        } = options;

        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);

        if ('cookieStore' in window) {
            window.cookieStore
                .set({
                    name: this.cookieName,
                    value: encodeURIComponent(value),
                    expires: date.getTime(),
                    path,
                    sameSite,
                })
                .catch((e: unknown) => {
                    console.error('CookieStore set failed', e);
                });

            return true;
        }

        let cookieString = `${this.cookieName}=${encodeURIComponent(value)}; expires=${date.toUTCString()}; path=${path}; SameSite=${sameSite}`;

        if (secure) {
            cookieString += '; Secure';
        }

        document.cookie = cookieString;
        return true;
    }

    /**
     * Remove the Cookie.
     */
    public remove(
        options: {
            path?: string;
        } = {},
    ): boolean {
        if (typeof document === 'undefined') return false;

        const { path = '/' } = options;

        if ('cookieStore' in window) {
            window.cookieStore
                .delete({
                    name: this.cookieName,
                    path,
                })
                .catch((e: unknown) => {
                    console.error('CookieStore delete failed', e);
                });

            return true;
        }

        document.cookie = `${this.cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path};`;
        return true;
    }
}
