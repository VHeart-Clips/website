import { TwitchClipContainer } from '@/components/TwitchClipContainer';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { evaluateclips } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import clsx from 'clsx';
import { ChevronLeft, ChevronRight, CircleX, Heart } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

type Item = {
    id: number;
    clipSlug: string;
    title: string;
};

export default function EvaluateClips() {
    const { t } = useTranslation('evaluateclips');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('evaluateclips'), href: evaluateclips().url },
    ];

    const items: Item[] = [
        {
            id: 1,
            clipSlug: 'StupidProtectiveScorpionTheRinger-rgF-JZJ3vPS2KKin',
            title: 'Clip 1',
        },
        {
            id: 2,
            clipSlug: 'SparklingCrunchyChoughNerfBlueBlaster-0eTNXxe7OLJpnyj_',
            title: 'Clip 2',
        },
        {
            id: 3,
            clipSlug: 'CourageousLazyBubbleteaStoneLightning-L4YWt7IyzpGD7wt7',
            title: 'Clip 3',
        },
    ];

    const [liked, setLiked] = useState<Set<number>>(new Set());
    const [activeIndex, setActiveIndex] = useState(0);
    const [skipped, setSkipped] = useState<Set<number>>(new Set());

    const containerRef = useRef<HTMLDivElement | null>(null);
    const itemRefs = useRef<(HTMLElement | null)[]>([]);

    function toggleLike(id: number) {
        setLiked((prev) => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    }

    function toggleSkip(id: number) {
        setSkipped((prev) => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    }

    function scrollToIndex(index: number) {
        const clamped = Math.max(0, Math.min(index, items.length - 1));
        const el = itemRefs.current[clamped];
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    useEffect(() => {
        if (!containerRef.current) return;

        const observer = new IntersectionObserver(
            (entries) => {
                const best = entries
                    .filter((e) => e.isIntersecting)
                    .sort(
                        (a, b) => b.intersectionRatio - a.intersectionRatio,
                    )[0];

                if (!best) return;
                const idx = Number((best.target as HTMLElement).dataset.index);
                if (!Number.isNaN(idx)) setActiveIndex(idx);
            },
            { root: containerRef.current, threshold: [0.6, 0.75, 0.9] },
        );

        itemRefs.current.forEach((el) => el && observer.observe(el));
        return () => observer.disconnect();
    }, []);

    return (
        <AppHeaderLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <header className="mb-3 space-y-1 pt-5 text-center sm:mb-4">
                <h1 className="text-base font-bold sm:text-xl 2xl:text-3xl">
                    {t('headline')}
                </h1>
                <p className="mx-auto max-w-2xl text-sm text-muted-foreground 2xl:text-base">
                    {t('subtitle')}
                </p>
            </header>

            <div className="mx-auto w-[95vw] max-w-3xl pt-5">
                <div className="relative aspect-video overflow-hidden rounded-xl border bg-background shadow-sm dark:shadow-none dark:ring-1 dark:ring-white/10">
                    <div
                        ref={containerRef}
                        className="scrollbar-none h-full snap-y snap-mandatory overflow-y-auto overscroll-contain"
                    >
                        {items.map((it, index) => {
                            const isActive = index === activeIndex;
                            const isSkipped = skipped.has(it.id);
                            const isLiked = liked.has(it.id);

                            const disableLike = isSkipped;
                            const disableSkip = isLiked;

                            return (
                                <section
                                    ref={(el) => {
                                        itemRefs.current[index] = el;
                                    }}
                                    data-index={index}
                                    key={it.id}
                                    className="flex h-full snap-start snap-always flex-col bg-black"
                                >
                                    {/* VIDEO */}
                                    <div className="relative min-h-0 flex-1 overflow-hidden">
                                        {isActive ? (
                                            <TwitchClipContainer
                                                slug={it.clipSlug}
                                                parent="localhost"
                                                className="absolute inset-0 h-full w-full p-2 sm:p-4"
                                            />
                                        ) : (
                                            <div className="absolute inset-0 grid place-items-center text-sm text-white/40">
                                                Clip bereit
                                            </div>
                                        )}
                                    </div>

                                    {/* ACTION BAR */}
                                    <div className="flex shrink-0 items-center justify-center gap-3 py-2 sm:gap-4 sm:py-3">
                                        {/* Previous */}
                                        <button
                                            type="button"
                                            onClick={() =>
                                                scrollToIndex(index - 1)
                                            }
                                            disabled={index === 0}
                                            className="grid size-9 place-items-center rounded-full bg-black/55 ring-1 ring-white/10 backdrop-blur transition-transform duration-150 ease-out active:scale-95 disabled:opacity-40 sm:size-11 sm:hover:scale-110"
                                        >
                                            <ChevronLeft className="h-4 w-4 text-white sm:h-5 sm:w-5" />
                                        </button>

                                        {/* Like */}
                                        <button
                                            type="button"
                                            aria-pressed={isLiked}
                                            disabled={disableLike}
                                            onClick={() => toggleLike(it.id)}
                                            className={clsx(
                                                'grid size-9 place-items-center rounded-full bg-black ring-1 ring-white/10 sm:size-11',
                                                'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                                disableLike && 'opacity-40',
                                            )}
                                        >
                                            <Heart
                                                className={clsx(
                                                    'h-4 w-4 sm:h-5 sm:w-5',
                                                    isLiked
                                                        ? 'text-red-500'
                                                        : 'text-white',
                                                )}
                                            />
                                        </button>

                                        {/* Skip */}
                                        <button
                                            type="button"
                                            aria-pressed={isSkipped}
                                            disabled={disableSkip}
                                            onClick={() => {
                                                toggleSkip(it.id);
                                                scrollToIndex(index + 1);
                                            }}
                                            className={clsx(
                                                'grid size-9 place-items-center rounded-full bg-black ring-1 ring-white/10 sm:size-11',
                                                'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                                disableSkip && 'opacity-40',
                                            )}
                                        >
                                            <CircleX
                                                className={clsx(
                                                    'h-4 w-4 sm:h-5 sm:w-5',
                                                    isSkipped
                                                        ? 'text-red-500'
                                                        : 'text-white',
                                                )}
                                            />
                                        </button>

                                        {/* Next */}
                                        <button
                                            type="button"
                                            onClick={() =>
                                                scrollToIndex(index + 1)
                                            }
                                            disabled={
                                                index === items.length - 1
                                            }
                                            className="grid size-9 place-items-center rounded-full bg-black/55 ring-1 ring-white/10 backdrop-blur transition-transform duration-150 ease-out active:scale-95 disabled:opacity-40 sm:size-11 sm:hover:scale-110"
                                        >
                                            <ChevronRight className="h-4 w-4 text-white sm:h-5 sm:w-5" />
                                        </button>
                                    </div>
                                </section>
                            );
                        })}
                    </div>
                </div>
            </div>
        </AppHeaderLayout>
    );
}
