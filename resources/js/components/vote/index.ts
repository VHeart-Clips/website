import clipVoteController from '@/actions/App/Http/Controllers/ClipVoteController';
import { PublicUser } from '@/types';
import { AlpineComponent } from 'alpinejs';

const MINIMUM_RATE_LIMIT = 6;
const INTERACTION_ARM_TIMEOUT = 3000;
const MAX_VOTE_RETRIES = 3;
const VOTE_RETRY_DELAY_MS = 1500;
const REQUEST_TIMEOUT_MS = 8000;

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

type VoteStatus = 'ok' | 'maintenance' | 'banned';
type VoteResult = { status: VoteStatus; clip?: ClipVoteResource | null };

export interface ClipVoteData extends ClipVoteConfig {
    timeLeft: number;
    isLoading: boolean;
    isMaintenanceMode: boolean;
    timer: ReturnType<typeof setInterval> | null;
    armedButton: 'like' | 'skip' | null;
    armTimeout: ReturnType<typeof setTimeout> | null;
    maintenanceRetryTimeout: ReturnType<typeof setTimeout> | null;
    isTouch: boolean;
    isTouchHandler: ((e: MediaQueryListEvent) => void) | null;
    isTouchQuery: MediaQueryList | null;
    keyboardHandler: ((e: KeyboardEvent) => void) | null;
    startTimer(seconds: number): void;
    arm(type: 'like' | 'skip'): Promise<void>;
    vote(decision: 0 | 1): Promise<void>;
    attemptVote(decision: 0 | 1, attempt: number): Promise<VoteResult>;
    isTextInput(el: HTMLElement | null): boolean;
    scheduleMaintenanceRetry(decision: 0 | 1): void;
}

export default (config: ClipVoteConfig): AlpineComponent<ClipVoteData> => ({
    ...config,
    timeLeft: 0,
    isLoading: false,
    isMaintenanceMode: false,
    timer: null,
    armedButton: null,
    armTimeout: null,
    maintenanceRetryTimeout: null,
    isTouch: false,
    isTouchHandler: null,
    isTouchQuery: null,
    keyboardHandler: null,

    init() {
        this.startTimer(config.initialDuration * 0.3);

        this.keyboardHandler = (e: KeyboardEvent) => {
            if (
                this.isLoading ||
                !this.hasClip ||
                this.timeLeft > 0 ||
                this.isMaintenanceMode
            )
                return;

            if (
                e.target instanceof HTMLElement &&
                this.isTextInput(e.target as HTMLElement)
            )
                return;

            if (e.key === 'ArrowLeft') void this.arm('like');
            if (e.key === 'ArrowRight') void this.arm('skip');
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
        if (this.maintenanceRetryTimeout)
            clearTimeout(this.maintenanceRetryTimeout);
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
        if (this.isLoading || !this.hasClip || this.isMaintenanceMode) return;

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
        if (this.isLoading || !this.hasClip || this.isMaintenanceMode) return;
        this.isLoading = true;
        this.reportItems = [];

        try {
            const result = await this.attemptVote(decision, 0);

            if (result.status === 'banned') {
                location.reload();
                return;
            }

            if (result.status === 'maintenance') {
                this.scheduleMaintenanceRetry(decision);
                return;
            }

            const nextClip = result.clip ?? null;

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
        } catch (err) {
            console.error('vote failed after retries:', err);
            this.scheduleMaintenanceRetry(decision);
        } finally {
            this.isLoading = false;
        }
    },

    async attemptVote(decision: 0 | 1, attempt: number): Promise<VoteResult> {
        try {
            const response = await window.axios.post(
                clipVoteController.store().url,
                {
                    clip_id: this.clipId,
                    voted: decision,
                },
                {
                    headers: { Accept: 'application/json' },
                    timeout: REQUEST_TIMEOUT_MS,
                },
            );

            if (typeof response.data === 'string') {
                return { status: 'maintenance' };
            }

            if (response.data?.ban === true) {
                return { status: 'banned' };
            }

            if (attempt > 0) {
                console.debug(`vote attempt ${attempt + 1} successful`);
            }

            return {
                status: 'ok',
                clip: response.data as ClipVoteResource | null,
            };
        } catch (err: unknown) {
            const hasServerResponse =
                err != null &&
                typeof err === 'object' &&
                'response' in err &&
                (err as { response?: unknown }).response != null;

            if (!hasServerResponse && attempt < MAX_VOTE_RETRIES) {
                const delay = VOTE_RETRY_DELAY_MS * (attempt + 1);
                console.warn(
                    `vote attempt ${attempt + 1} no response, retry in ${delay}ms`,
                );
                await new Promise<void>((res) => setTimeout(res, delay));
                return this.attemptVote(decision, attempt + 1);
            }

            throw err;
        }
    },

    scheduleMaintenanceRetry(decision: 0 | 1) {
        this.isMaintenanceMode = true;
        this.armedButton = null;

        if (this.maintenanceRetryTimeout)
            clearTimeout(this.maintenanceRetryTimeout);

        this.maintenanceRetryTimeout = setTimeout(async () => {
            try {
                const response = await window.axios.post(
                    clipVoteController.store().url,
                    {
                        clip_id: this.clipId,
                        voted: decision,
                    },
                    {
                        headers: { Accept: 'application/json' },
                        timeout: REQUEST_TIMEOUT_MS,
                    },
                );
                if (typeof response.data !== 'string') {
                    window.location.reload();
                } else {
                    this.scheduleMaintenanceRetry(decision);
                }
            } catch {
                this.scheduleMaintenanceRetry(decision);
            }
        }, 5000);
    },

    isTextInput(el: HTMLElement | null) {
        if (!el) return false;
        const tag = (el.tagName || '').toLowerCase();
        return tag === 'input' || tag === 'textarea' || el.isContentEditable;
    },
});
