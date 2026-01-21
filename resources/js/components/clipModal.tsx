import { TwitchClipContainer } from '@/components/TwitchClipContainer';
import { CircleX, Heart, X } from 'lucide-react';

export type ClipModalItem = {
    id: number;
    clipSlug: string;
    title: string;
};

export function ClipModal({
    openClip,
    twitchParent,
    isLiked,
    isSkipped,
    disableLike,
    disableSkip,
    onClose,
    onToggleLike,
    onToggleSkip,
}: {
    openClip: ClipModalItem;
    twitchParent: string;
    isLiked: boolean;
    isSkipped: boolean;
    disableLike: boolean;
    disableSkip: boolean;
    onClose: () => void;
    onToggleLike: (id: number) => void;
    onToggleSkip: (id: number) => void;
}) {
    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 dark:bg-white/10"
            onClick={onClose}
            role="dialog"
            aria-modal="true"
            aria-label={`Clip ansehen: ${openClip.title}`}
        >
            <div
                className="flex h-[70svh] w-[80vw] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:w-[45vw] 2xl:w-[55vw] dark:bg-black"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div className="text-blac flex items-center justify-between px-4 py-3 2xl:px-8 dark:text-white">
                    <div className="line-clamp-2 pr-2 text-xs font-semibold sm:text-sm 2xl:text-lg">
                        {openClip.title}
                    </div>

                    <button
                        type="button"
                        className="shrink-0 rounded-md bg-white text-black dark:bg-black dark:text-white"
                        onClick={onClose}
                        aria-label="Schließen"
                    >
                        <X className="h-5 w-5 2xl:h-7 2xl:w-7" />
                    </button>
                </div>

                {/* Video */}
                <div className="min-h-0 flex-1 px-0 2xl:px-4">
                    <div className="flex h-full min-h-0 items-center justify-center overflow-hidden">
                        <div className="relative aspect-video h-full w-auto">
                            <TwitchClipContainer
                                slug={openClip.clipSlug}
                                parent={twitchParent}
                                className="absolute inset-0 h-full w-full"
                            />
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-center gap-3 py-3 2xl:gap-5 2xl:py-4">
                    {/* Like */}
                    <button
                        type="button"
                        aria-pressed={isLiked}
                        disabled={disableLike}
                        onClick={() => onToggleLike(openClip.id)}
                        className={`grid size-8 place-items-center rounded-full bg-white transition-transform duration-150 2xl:size-12 dark:bg-black ${
                            disableLike
                                ? 'cursor-not-allowed opacity-40'
                                : 'active:scale-95 sm:hover:scale-110'
                        }`}
                    >
                        <Heart
                            className={`h-6 w-6 2xl:h-8 2xl:w-8 ${
                                isLiked
                                    ? 'text-red-500'
                                    : 'text-gray-700 hover:text-red-500 dark:text-white dark:hover:text-red-500'
                            }`}
                        />
                    </button>

                    {/* Skip */}
                    <button
                        type="button"
                        aria-pressed={isSkipped}
                        disabled={disableSkip}
                        onClick={() => onToggleSkip(openClip.id)}
                        className={`bg:white grid size-8 place-items-center rounded-full transition-transform duration-150 2xl:size-12 dark:bg-black ${
                            disableSkip
                                ? 'cursor-not-allowed opacity-40'
                                : 'active:scale-95 sm:hover:scale-110'
                        }`}
                    >
                        <CircleX
                            className={`h-6 w-6 2xl:h-8 2xl:w-8 ${
                                isSkipped
                                    ? 'text-red-500'
                                    : 'text-gray-700 hover:text-red-500 dark:text-white dark:hover:text-red-500'
                            }`}
                        />
                    </button>
                </div>
            </div>
        </div>
    );
}
