import useInstantInView from '@/hooks/use-instant-in-view';
import { cn } from '@/lib/utils';
import { PublicClip } from '@/types';
import clsx from 'clsx';
import { Clock, Heart, Image as ImageIcon, ImageOff } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useInView } from 'react-intersection-observer';

type ClipPreviewProps = {
    clip: PublicClip;
    onClick?: () => void;
    hideTitle?: boolean;
};

const formatDuration = (seconds: number) => {
    const m = Math.floor(seconds / 60);
    const s = Math.round(seconds % 60);
    return `${m}:${String(s).padStart(2, '0')}`;
};

type ImageStatus = 'loading' | 'loaded' | 'error';

export function ClipPreview({ clip, onClick, hideTitle }: ClipPreviewProps) {
    const [imageStatus, setImageStatus] = useState<ImageStatus>('loading');
    const imgRef = useRef<HTMLImageElement>(null);
    const { isMountedInView, ref: viewportRef } = useInstantInView(100);

    const { ref: observerRef, inView } = useInView({
        triggerOnce: true,
        rootMargin: '100px',
        skip: isMountedInView,
    });

    const shouldRender = isMountedInView || inView;

    useEffect(() => {
        const img = imgRef.current;
        async function updateState(state: ImageStatus) {
            setImageStatus(state);
        }

        if (img && img.complete) {
            // If image is already cached/loaded, update state immediately
            if (img.naturalWidth > 0) {
                void updateState('loaded');
            } else {
                void updateState('error');
            }
        }
    }, [shouldRender]);

    const thumbnail = useMemo(() => {
        if (!shouldRender) {
            return null;
        }

        return (
            <img
                ref={imgRef}
                src={clip.thumbnail_url}
                alt={clip.title}
                className={`h-full w-full object-cover ${
                    imageStatus === 'loaded' ? 'opacity-100' : 'opacity-0'
                }`}
                loading="lazy"
                decoding="async"
                onLoad={() => setImageStatus('loaded')}
                onError={() => setImageStatus('error')}
            />
        );
    }, [clip.thumbnail_url, clip.title, imageStatus, shouldRender]);

    return (
        <button
            ref={(node) => {
                observerRef(node);
                viewportRef.current = node;
            }}
            type="button"
            onClick={onClick}
            aria-label={`Clip öffnen: ${clip.title}`}
            className="group focus-visible:ring-primary-500 relative aspect-video w-full overflow-hidden rounded-md bg-gray-200 outline-none focus-visible:ring-2 dark:bg-gray-800"
        >
            {/* Loading */}
            {imageStatus === 'loading' && (
                <div className="absolute inset-0 flex items-center justify-center bg-gray-200 text-gray-400 dark:bg-gray-800 dark:text-gray-600">
                    <ImageIcon className="size-12 animate-pulse" />
                </div>
            )}

            {/* Error */}
            {imageStatus === 'error' && (
                <div className="absolute inset-0 flex items-center justify-center bg-gray-300 text-gray-500 dark:bg-gray-800 dark:text-gray-500">
                    <ImageOff className="size-12" />
                </div>
            )}

            {/* Image */}
            {thumbnail}

            {/* Länge */}
            <div className="absolute top-2 left-2 flex items-center gap-1 rounded-lg bg-black/60 px-1.5 py-0.5 text-white backdrop-blur-[2px] transition-colors group-hover:bg-black/85 sm:px-2 sm:py-1 sm:text-xs">
                <Clock
                    className="size-3 sm:size-4 md:size-6"
                    aria-hidden="true"
                />
                <p className={'sr-only'}>Länge</p>
                <span className={'font-mono text-sm md:text-base'}>
                    {formatDuration(clip.clip_duration)}
                </span>
            </div>

            {/* Likes */}
            <div className="absolute top-2 right-2 flex items-center gap-1 rounded-lg bg-black/60 px-1.5 py-0.5 text-white backdrop-blur-[2px] transition-colors group-hover:bg-black/85 sm:px-2 sm:py-1 sm:text-xs">
                <Heart
                    className="size-3 text-red-500 sm:size-4 md:size-6"
                    aria-hidden="true"
                />
                <p className={'sr-only'}>Stimmen</p>
                <span className={'text-sm md:text-base lg:text-lg'}>
                    {clip.votes ?? 0}
                </span>
            </div>

            {/* Titel unten */}
            <div
                className={cn(
                    'absolute right-2 bottom-1 left-2 rounded-xl bg-black/75 px-2 py-1 text-white backdrop-blur-[2px] transition-colors group-hover:bg-black/85 sm:bottom-2',
                    hideTitle ? 'hidden' : '',
                )}
            >
                <div className="line-clamp-1 text-xs font-medium sm:text-sm">
                    {clip.title}
                </div>

                {clip.broadcaster && (
                    <div className="truncate text-white/80 sm:text-xs">
                        {clip.broadcaster.name}
                    </div>
                )}
            </div>
        </button>
    );
}
