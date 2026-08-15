import { AlpineComponent } from 'alpinejs';

export interface FullscreenToggleData {
    fullscreen: boolean;
    supported: boolean;
    handleFullscreenChange?(): void;
    toggle(): void;
}

/**
 * Full screen toggle thingy
 * will not work on iOS devices because of apple reasons
 */
export default (): AlpineComponent<FullscreenToggleData> => ({
    fullscreen: false,
    supported: 'requestFullscreen' in document.documentElement,
    init() {
        this.handleFullscreenChange = () => {
            this.fullscreen = !!document.fullscreenElement;
        };

        document.addEventListener(
            'fullscreenchange',
            this.handleFullscreenChange,
        );

        this.handleFullscreenChange();
    },
    destroy() {
        if (!this.handleFullscreenChange) return;

        document.removeEventListener(
            'fullscreenchange',
            this.handleFullscreenChange,
        );
    },
    toggle() {
        if (!document.fullscreenElement) {
            void document.documentElement.requestFullscreen();
        } else {
            void document.exitFullscreen();
        }
    },
});
