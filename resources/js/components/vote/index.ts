import clipVoteController from '@/actions/App/Http/Controllers/ClipVoteController';
import { PublicUser } from '@/types';
import { AlpineComponent } from 'alpinejs';

const MINIMUM_RATE_LIMIT = 6;
const INTERACTION_ARM_TIMEOUT = 3000;

/**
 * @resource App\Http\Resources\Clip\ClipVoteResource
 * @see App/Http/Resources/Clip/ClipVoteResource.php
 */
export type ClipVoteResource = {
    id: number;
    slug: string;
    title: string;
    duration: number;
    url: string;
    thumbnail_url: string;
    broadcaster: PublicUser;
};

export interface ClipVoteConfig {
    clipTwitchId: string;
    clipId: number | null;
    clipBroadcasterAvatar: string;
    clipBroadcasterUrl: string;
    clipBroadcasterName: string;
    hasBroadcaster: boolean;
    hasClip: boolean;
    initialDuration: number;
    reportItems: { type: string; id: number }[] | null;
}

export interface ClipVoteData extends ClipVoteConfig {
    timeLeft: number;
    isLoading: boolean;
    timer: ReturnType<typeof setInterval> | null;
    armedButton: 'like' | 'skip' | null;
    armTimeout: ReturnType<typeof setTimeout> | null;
    isTouch: boolean;
    isTouchHandler: ((e: MediaQueryListEvent) => void) | null;
    isTouchQuery: MediaQueryList | null;
    keyboardHandler: ((e: KeyboardEvent) => void) | null;
    startTimer(seconds: number): void;
    arm(type: 'like' | 'skip'): Promise<void>;
    vote(decision: 0 | 1): Promise<void>;
    isTextInput(el: HTMLElement | null): boolean;

}

export default (config: ClipVoteConfig): AlpineComponent<ClipVoteData> => ({
    ...config,
    timeLeft: 0,
    isLoading: false,
    timer: null,
    armedButton: null,
    armTimeout: null,
    isTouch: false,
    isTouchHandler: null,
    isTouchQuery: null,
    keyboardHandler: null,

    init() {
        this.startTimer(config.initialDuration * 0.3);

        this.keyboardHandler = (e: KeyboardEvent) => {
            if (this.isLoading || !this.hasClip || this.timeLeft > 0) return;

            if(e.target instanceof HTMLElement && this.isTextInput(e.target as HTMLElement)) return;

            if (e.key === 'ArrowLeft')
                void this.arm('like');
            if (e.key === 'ArrowRight')
                void this.arm('skip');
        };

        this.isTouchQuery = window.matchMedia('(pointer: coarse)');
        this.isTouch = this.isTouchQuery.matches;

        this.isTouchHandler = (e) => {
            console.debug('Is Touch', e.matches);
            this.isTouch = e.matches;
        };

        window.addEventListener('keydown', this.keyboardHandler);
        this.isTouchQuery.addEventListener('change', this.isTouchHandler);
    },

    destroy() {
        if (this.keyboardHandler) {
            window.removeEventListener('keydown', this.keyboardHandler);
        }

        if (this.isTouchQuery && this.isTouchHandler) {
            this.isTouchQuery.removeEventListener(
                'change',
                this.isTouchHandler,
            );
        }

        if (this.timer) clearInterval(this.timer);
        if (this.armTimeout) clearTimeout(this.armTimeout);
    },

    startTimer(seconds: number) {
        this.timeLeft =
            !seconds || seconds < MINIMUM_RATE_LIMIT
                ? MINIMUM_RATE_LIMIT
                : Math.round(seconds);

        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
            } else {
                clearInterval(this.timer!);
            }
        }, 1000);
    },

    async arm(type: 'like' | 'skip') {
        if (this.isLoading || !this.hasClip) return;

        if (!this.isTouch) {
            await this.vote(type === 'like' ? 1 : 0);
            return;
        }

        if (this.armedButton === type) {
            this.armedButton = null;
            clearTimeout(this.armTimeout!);
            await this.vote(type === 'like' ? 1 : 0);
            return;
        }

        this.armedButton = type;
        clearTimeout(this.armTimeout!);
        this.armTimeout = setTimeout(() => {
            this.armedButton = null;
        }, INTERACTION_ARM_TIMEOUT);
    },

    async vote(decision: 0 | 1) {
        if (this.isLoading || !this.hasClip) return;
        this.isLoading = true;
        this.reportItems = [];

        try {
            const response = await window.axios.post(
                clipVoteController.store().url,
                {
                    voted: decision,
                },
                {
                    headers: { Accept: 'application/json' },
                },
            );

            const nextClip: ClipVoteResource | null = response.data;

            if (nextClip?.id) {
                this.hasClip = true;
                this.clipTwitchId = nextClip.slug;
                this.clipId = nextClip.id;
                this.reportItems = [{ type: 'clip', id: this.clipId }];
                this.clipBroadcasterAvatar = nextClip.broadcaster.avatar;
                this.clipBroadcasterUrl = `https://twitch.tv/${nextClip.broadcaster.name}`;
                this.clipBroadcasterName = nextClip.broadcaster.name;
                this.hasBroadcaster = !!nextClip.broadcaster;
                this.startTimer(nextClip.duration * 0.3);
            } else {
                this.hasClip = false;
                this.clipTwitchId = '';
                this.clipId = null;
                this.reportItems = null;
                this.clipBroadcasterAvatar = '';
                this.clipBroadcasterUrl = '';
                this.clipBroadcasterName = '';
                this.hasBroadcaster = false;
            }
        } finally {
            this.isLoading = false;
        }
    },

    isTextInput (el: HTMLElement | null) {
        if (!el) return false;
        const tag = (el.tagName || '').toLowerCase();
        return tag === 'input' || tag === 'textarea' || el.isContentEditable;
    },
});
