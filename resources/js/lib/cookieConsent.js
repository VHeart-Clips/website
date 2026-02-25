class CookieConsent {
    constructor() {
        this.cookieName = 'vheart_cookie_consent';
        this.listeners = new Set();
        this.check = this.#check.bind(this);

        this.intervalId = null;

        this.state = this.#read();
        this.#init();

        if (typeof window !== 'undefined') {
            window.CookieConsent = this;
        }
    }

    #read() {
        if (typeof document === 'undefined') return {};

        const match = document.cookie.match(
            new RegExp(`(^|;\\s*)(${this.cookieName})=([^;]*)`),
        );
        if (match && match[3]) {
            try {
                return JSON.parse(decodeURIComponent(match[3]));
            } catch (e) {
                console.debug(e);
                return {};
            }
        }
        return {};
    }

    #check() {
        const currentState = this.#read();
        if (JSON.stringify(currentState) !== JSON.stringify(this.state)) {
            this.state = currentState;
            this.#clearDeniedCookies();
            this.listeners.forEach((l) => l(this.state));
        }
    }

    #init() {
        if (typeof window === 'undefined') return;

        if ('cookieStore' in window) {
            window.cookieStore.addEventListener('change', (event) => {
                const changed = event.changed.some(
                    (c) => c.name === this.cookieName,
                );
                const deleted = event.deleted.some(
                    (c) => c.name === this.cookieName,
                );

                if (changed || deleted) this.#check();
            });
        }

        this.#clearDeniedCookies();
    }

    /**
     * Subscribe to Cookie consent changes
     * If cookieStore is not available, it will start a 1-second interval that checks the current state
     * Interval will be stopped if there are no listeners left
     * @param {function(ConsentState): void} listener - Callback receives new state
     * @returns {function(): boolean} - Unsubscribe function
     */
    subscribe(listener) {
        this.listeners.add(listener);

        if (
            this.listeners.size === 1 &&
            !this.intervalId &&
            typeof window !== 'undefined' &&
            !('cookieStore' in window)
        ) {
            this.intervalId = setInterval(this.#check.bind(this), 1000);
        }

        return () => {
            this.listeners.delete(listener);

            if (this.listeners.size === 0 && this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        };
    }

    /**
     * Get current State
     */
    getState() {
        return this.state;
    }

    #clearDeniedCookies() {
        if (typeof document === 'undefined' || !this.state) return;

        Object.keys(this.state).forEach((key) => {
            if (this.state[key] === false) {
                console.debug('CookieConsent: Removing', key);
                this.deleteCookie(key);
            }
        });
    }

    deleteCookie(name) {
        if ('cookieStore' in window && window.cookieStore.delete) {
            window.cookieStore.delete(name).catch((reason) => {
                console.warn(
                    'CookieConsent: could not remove cookie',
                    name,
                    reason,
                );
            });
        } else {
            const hostname = window.location.hostname;
            const path = '/';
            const epoch = 'Thu, 01 Jan 1970 00:00:01 GMT';

            document.cookie = `${name}=; expires=${epoch}; path=${path}; domain=.${hostname}`;
            document.cookie = `${name}=; expires=${epoch}; path=${path}; domain=${hostname}`;
            document.cookie = `${name}=; expires=${epoch}; path=${path};`;
        }
    }
}
export default CookieConsent;
export const cookieConsentManager = new CookieConsent();
