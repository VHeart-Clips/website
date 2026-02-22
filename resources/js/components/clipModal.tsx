import TwitchClipEmbed from '@/components/embeds/twitch-clip-embed';
import ReportButton, {
    ReportableItem,
} from '@/components/reports/report-button';
import T from '@/components/t';
import { vote } from '@/routes';
import { PublicClip } from '@/types';
import { Link } from '@inertiajs/react';
import { X } from 'lucide-react';

export function ClipModal({
    clip,
    onClose,
}: {
    clip: PublicClip;
    onClose: () => void;
}) {
    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 dark:bg-white/10"
            onClick={onClose}
            role="dialog"
            aria-modal="true"
            aria-label={`Clip ansehen: ${clip.title}`}
        >
            <div
                className="flex h-[70svh] w-[80vw] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:w-[45vw] 2xl:w-[55vw] dark:bg-black"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div className="text-blac flex items-center justify-between px-4 py-3 2xl:px-8 dark:text-white">
                    <div className="line-clamp-2 pr-2 text-xs font-semibold sm:text-sm 2xl:text-lg">
                        {clip.title}
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
                        <div className="relative aspect-video h-full">
                            <TwitchClipEmbed
                                slug={clip.slug}
                                thumbnail={clip.thumbnail_url}
                                className="absolute inset-0 h-full w-full"
                            />
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-center gap-3 py-3 2xl:gap-5 2xl:py-4">
                    <Link type={'button'} href={vote().url}>
                        <T ns={'vote'} k={'call_to_action'} />
                    </Link>

                    <ReportButton
                        items={[
                            {
                                type: 'clip',
                                id: clip.id,
                            },
                            clip.broadcaster?.id && {
                                type: 'user',
                                id: clip.broadcaster.id,
                                label: 'broadcaster',
                            },
                        ].filter((item): item is ReportableItem =>
                            Boolean(item),
                        )}
                    />
                </div>
            </div>
        </div>
    );
}
