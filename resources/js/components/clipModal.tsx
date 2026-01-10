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
            className="fixed inset-0 z-50 grid place-items-center bg-black/80 p-4"
            onClick={onClose}
            role="dialog"
            aria-modal="true"
            aria-label={`Clip ansehen: ${openClip.title}`}
        >
            <div
                className="w-3/4 max-w-7xl overflow-hidden bg-black shadow-2xl"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="flex items-center justify-between p-5 text-white">
                    <div className="text-xl font-semibold">
                        {openClip.title}
                    </div>

                    <button
                        type="button"
                        className="rounded-md bg-black text-white"
                        onClick={onClose}
                        aria-label="Schließen"
                    >
                        <X className="h-8 w-8" />
                    </button>
                </div>

                <div className="relative aspect-video overflow-hidden">
                    <TwitchClipContainer
                        slug={openClip.clipSlug}
                        parent={twitchParent}
                        className="absolute inset-0 h-full w-full"
                    />
                </div>

                <div className="flex items-center justify-center gap-6 py-6">
                    {/* Like */}
                    <button
                        type="button"
                        aria-pressed={isLiked}
                        disabled={disableLike}
                        onClick={() => onToggleLike(openClip.id)}
                        className={`grid size-14 place-items-center rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ${
                            disableLike
                                ? 'cursor-not-allowed opacity-40'
                                : 'active:scale-95 sm:hover:scale-110'
                        }`}
                    >
                        <Heart
                            className={`h-10 w-10 ${
                                isLiked ? 'text-red-500' : 'text-white'
                            }`}
                        />
                    </button>

                    {/* Skip */}
                    <button
                        type="button"
                        aria-pressed={isSkipped}
                        disabled={disableSkip}
                        onClick={() => onToggleSkip(openClip.id)}
                        className={`grid size-14 place-items-center rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ${
                            disableSkip
                                ? 'cursor-not-allowed opacity-40'
                                : 'active:scale-95 sm:hover:scale-110'
                        }`}
                    >
                        <CircleX
                            className={`h-10 w-10 ${
                                isSkipped ? 'text-red-500' : 'text-white'
                            }`}
                        />
                    </button>
                </div>
            </div>
        </div>
    );
}
