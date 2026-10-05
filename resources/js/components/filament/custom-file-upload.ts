import type { AlpineComponent } from 'alpinejs';
import axios, { type AxiosProgressEvent } from 'axios';

const MAX_RETRIES = 3;
const RETRY_DELAY_MS = 1000;
const YURA_DELAY_MS = 3000;
const YURA_PROGRESS_THRESHOLD = 50;
const TMP_PREFIX = 'tmp/';

const s3 = axios.create();

interface Presigned {
    key: string;
    url: string;
    headers: Record<string, string>;
}

interface Config {
    state: string | null;
    presignUrl: string;
    configToken: string;
}

interface UploadData {
    state: string | null;
    previousState: string | null;
    lastFileKey: string | null;
    progress: number;
    speed: number;
    eta: number;
    uploading: boolean;
    error: string | null;
    retries: number;
    maxRetries: number;
    abortController: AbortController | null;
    yuraVisible: boolean;
    yuraTimer: ReturnType<typeof setTimeout> | null;
    readonly stats: string;
    readonly fileName: string;
    readonly isTemporary: boolean;
    destroy(): void;
    remove(): void;
    clearYura(): void;
    resetProgress(): void;
    onProgress(e: AxiosProgressEvent): void;
    upload(file?: File): Promise<void>;
}

const isNetworkError = (e: unknown) =>
    axios.isAxiosError(e) && !e.response && !axios.isCancel(e);

function errorMessage(e: unknown): string {
    if (axios.isCancel(e)) return 'Upload canceled';

    if (axios.isAxiosError(e)) {
        if (!e.response) return 'Network error';

        if (e.response.status === 429) {
            return (
                e.response.data?.message ??
                `Too many attempts, please wait ${e?.response.headers['retry-after']} seconds and try again.`
            );
        }

        return (
            e.response.data?.message ?? `Upload failed (${e.response.status})`
        );
    }

    return (e as Error).message;
}

async function retryOnNetworkError<T>(
    fn: () => Promise<T>,
    onRetry: () => void,
): Promise<T> {
    for (let attempt = 0; ; attempt++) {
        try {
            return await fn();
        } catch (e) {
            if (!isNetworkError(e) || attempt >= MAX_RETRIES) throw e;

            onRetry();
            await new Promise((r) =>
                setTimeout(r, RETRY_DELAY_MS * 2 ** attempt),
            );
        }
    }
}

function formatEta(seconds: number): string {
    if (seconds < 60) return `${Math.round(seconds)}s`;

    return `${Math.floor(seconds / 60)}m ${Math.round(seconds % 60)}s`;
}

export default function customFileUpload({
    state,
    presignUrl,
    configToken,
}: Config): AlpineComponent<UploadData> {
    return {
        state,
        previousState: null,
        lastFileKey: null,
        progress: 0,
        speed: 0,
        eta: 0,
        uploading: false,
        error: null,
        retries: 0,
        maxRetries: MAX_RETRIES,
        abortController: null,
        yuraVisible: false,
        yuraTimer: null,

        get stats() {
            if (!this.speed) return '~.~ MB/s · ~~s';

            return `${(this.speed / 1048576).toFixed(1)} MB/s · ${formatEta(this.eta)}`;
        },

        get fileName() {
            return typeof this.state === 'string'
                ? (this.state.split('/').pop() ?? '')
                : '';
        },

        get isTemporary() {
            return (
                typeof this.state === 'string' &&
                this.state.startsWith(TMP_PREFIX)
            );
        },

        destroy() {
            this.clearYura();

            if (this.abortController) {
                this.abortController.abort('User Cancelled');
                this.abortController = null;

                new window.FilamentNotification()
                    .title('Upload Cancelled')
                    .warning()
                    .send();
            }
        },

        remove() {
            this.state = null;
            this.error = null;
        },

        clearYura() {
            if (this.yuraTimer) clearTimeout(this.yuraTimer);
            this.yuraTimer = null;
            this.yuraVisible = false;
        },

        resetProgress() {
            this.progress = this.speed = this.eta = 0;
            this.clearYura();
        },

        onProgress(e) {
            if (e.total) this.progress = Math.round((e.loaded / e.total) * 100);
            this.speed = e.rate ?? 0;
            this.eta = e.estimated ?? 0;

            if (this.yuraTimer) clearTimeout(this.yuraTimer);
            this.yuraTimer =
                this.progress >= YURA_PROGRESS_THRESHOLD
                    ? setTimeout(() => (this.yuraVisible = true), YURA_DELAY_MS)
                    : null;
        },

        async upload(file?: File) {
            if (!file) return;

            const fileKey = `${file.name}:${file.size}:${file.lastModified}`;

            if (
                fileKey === this.lastFileKey &&
                (this.uploading || this.state)
            ) {
                return;
            }

            this.lastFileKey = fileKey;
            this.abortController?.abort();
            const controller = (this.abortController = new AbortController());
            const { signal } = controller;

            const send = async () => {
                const { data } = await axios.post<Presigned>(
                    presignUrl,
                    {
                        token: configToken,
                        mime: file.type,
                        size: file.size,
                        previous: this.previousState,
                    },
                    { signal },
                );

                await s3.put(data.url, file, {
                    headers: data.headers,
                    signal,
                    onUploadProgress: (e) => this.onProgress(e),
                });

                return data.key;
            };

            this.error = null;
            this.retries = 0;
            this.resetProgress();
            this.uploading = true;
            this.$dispatch('form-processing-started', {
                message: 'Uploading',
            });

            try {
                this.state = this.previousState = await retryOnNetworkError(
                    send,
                    () => {
                        this.retries++;
                        this.resetProgress();
                    },
                );
            } catch (e) {
                if (!axios.isCancel(e)) {
                    console.debug('Error while upload: ', e);
                    this.error = errorMessage(e);
                    this.lastFileKey = null;

                    if (this.previousState && this.state === this.previousState)
                        this.state = null;
                }
            } finally {
                if (this.abortController === controller) {
                    this.abortController = null;
                    this.uploading = false;
                    this.clearYura();
                    this.$dispatch('form-processing-finished');
                }
            }
        },
    };
}
