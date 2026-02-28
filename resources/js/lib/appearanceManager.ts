import CookieManager from '@/lib/cookieManager';

export type Appearance = 'light' | 'dark' | 'system';

export class AppearanceManager {
    private cookieManager: CookieManager;

    constructor() {
        this.cookieManager = new CookieManager('appearance');
        this.applyAppearance();
    }

    public applyAppearance() {
        const appearance = this.getAppearance();

        const isConfiguredDark = appearance === 'dark';
        const isSystemDark =
            appearance === 'system' && this.getMediaQuery()?.matches;
        const isDark = isConfiguredDark || isSystemDark;

        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
    }

    public setAppearance(newAppearance: Appearance): void {
        if (this.getAppearance() === newAppearance) {
            return;
        }

        if (newAppearance === 'system') {
            localStorage.removeItem('appearance');
            this.cookieManager.remove();
        } else {
            localStorage.setItem('appearance', newAppearance);
            this.cookieManager.set(newAppearance);
        }

        this.applyAppearance();
    }

    public getMediaQuery() {
        return window?.matchMedia('(prefers-color-scheme: dark)');
    }

    public getAppearance(): Appearance {
        const localStorageConfig = localStorage.getItem(
            'appearance',
        ) as Appearance;
        const cookieConfig = this.cookieManager.get();

        return localStorageConfig || cookieConfig || 'system';
    }
}
