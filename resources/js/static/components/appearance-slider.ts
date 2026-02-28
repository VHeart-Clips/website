import { AlpineComponent } from 'alpinejs';
import { Appearance, AppearanceManager } from '@/lib/appearanceManager';

export interface AppearanceSliderData {
    appearance: Appearance | null;
    items: Appearance[];
    appearanceManager: AppearanceManager;
    activeIndex: number;
    updateAppearance(newAppearance: Appearance): void;
    handleAppearanceChange?(): void;
}

export default (): AlpineComponent<AppearanceSliderData> => ({
    appearance: null,
    appearanceManager: new AppearanceManager(),
    items: ['light', 'dark', 'system'],
    init() {
        // weird but "this" is actually annoying if we want to clean up properly later lol
        this.handleAppearanceChange = () => {
            this.appearance = this.appearanceManager.getAppearance();
        };

        window.addEventListener(
            'appearanceChanged',
            this.handleAppearanceChange,
        );

        this.handleAppearanceChange();
    },
    destroy() {
        if (!this.handleAppearanceChange) return;

        window.removeEventListener(
            'appearanceChanged',
            this.handleAppearanceChange,
        );
    },
    get activeIndex() {
        if (! this.appearance) {
            return -1;
        }

        return Math.max(0, this.items.indexOf(this.appearance));
    },
    updateAppearance(newAppearance) {
        this.appearance = newAppearance;
        this.appearanceManager.setAppearance(newAppearance);
    },
});
