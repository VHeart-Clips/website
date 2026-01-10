import { store } from '@/actions/App/Http/Controllers/ClipVoteController';
import AppLayout from '@/layouts/app-layout';
import { vote } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import clsx from 'clsx';
import {
    ChevronLeft,
    ChevronRight,
    CircleX,
    Heart,
    Loader,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

type Item = {
    id: number;
    clipSlug: string;
    title: string;
};

type PageProps = {
    clip: Clip;
};

type Clip = {
    id: number;
    twitch_id: string;
    title: string;
    public_votes: number;
};

export default function EvaluateClips() {
    const { t } = useTranslation('evaluateclips');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('evaluateclips'), href: vote().url },
    ];

    const items: Item[] = [];

    const { props } = usePage<PageProps>();

    const [liked, setLiked] = useState<Set<number>>(new Set());
    const [activeIndex, setActiveIndex] = useState(0);
    const [skipped, setSkipped] = useState<Set<number>>(new Set());

    const containerRef = useRef<HTMLDivElement | null>(null);
    const itemRefs = useRef<(HTMLElement | null)[]>([]);

    const getClip = () => {
        console.log('getClip');
        router.reload({ only: ['clip'] });
    };

    if (props.clip) {
        items.push({
            id: props.clip.id,
            clipSlug: props.clip.twitch_id,
            title: props.clip.title,
        } as Item);
    }

    function toggleLike(id: number) {
        setLiked((prev) => {
            const next = new Set(prev);
            next.has(id) ? next.delete(id) : next.add(id);
            return next;
        });
        console.log('like', props.clip);
    }

    function toggleSkip(id: number) {
        setSkipped((prev) => {
            const next = new Set(prev);
            next.has(id) ? next.delete(id) : next.add(id);
            return next;
        });
        console.log('skip', props.clip);
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

        if (!props.clip) {
            getClip();
        }

        itemRefs.current.forEach((el) => el && observer.observe(el));
        return () => observer.disconnect();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <div className="relative mx-auto w-full max-w-3xl px-2 py-2">
                <header className="mb-4 space-y-1 text-center">
                    <h1 className="text-xl font-bold sm:text-2xl md:text-3xl">
                        {t('headline')}
                    </h1>
                    <p className="mx-auto max-w-2xl text-sm text-muted-foreground sm:text-base">
                        {t('subtitle')}
                    </p>
                </header>

                <div className="relative overflow-hidden rounded-xl border bg-background shadow-sm dark:shadow-none dark:ring-1 dark:ring-white/10">
                    <div
                        ref={containerRef}
                        className="scrollbar-none h-[calc(100dvh-320px)] snap-y snap-mandatory overflow-y-auto overscroll-contain"
                    >
                        {items.length == 0 ? (
                            <div className="absolute inset-0 grid place-items-center text-sm text-white/40">
                                <Loader></Loader>
                            </div>
                        ) : (
                            items.map((it, index) => {
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
                                        className="flex h-[calc(100dvh-320px)] snap-start snap-always flex-col bg-black"
                                    >
                                        {/* VIDEO */}
                                        <div className="relative flex-1 overflow-hidden rounded-xl">
                                            {isActive ? (
                                                <iframe
                                                    src={`https://clips.twitch.tv/embed?clip=${it.clipSlug}&parent=localhost&autoplay=false&muted=false`}
                                                    className="absolute inset-0 h-full w-full"
                                                    allow="fullscreen"
                                                    allowFullScreen
                                                    title={it.title}
                                                />
                                            ) : (
                                                <div className="absolute inset-0 grid place-items-center text-sm text-white/40">
                                                    Clip bereit
                                                </div>
                                            )}
                                        </div>

                                        {/* ACTION BAR */}
                                        <div className="flex shrink-0 items-center justify-center gap-4 py-4">
                                            {/* Previous */}
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    scrollToIndex(index - 1)
                                                }
                                                disabled={index === 0}
                                                className="grid size-12 place-items-center rounded-full bg-black/55 ring-1 ring-white/10 backdrop-blur transition-transform duration-150 ease-out active:scale-95 disabled:opacity-40 sm:size-14 sm:hover:scale-110"
                                            >
                                                <ChevronLeft className="h-6 w-6 text-white" />
                                            </button>

                                            {/* Like */}
                                            <Link
                                                as="button"
                                                type="button"
                                                aria-pressed={isLiked}
                                                disabled={disableLike}
                                                onClick={() =>
                                                    toggleLike(it.id)
                                                }
                                                href={store()}
                                                data={{
                                                    clip: it.id,
                                                    voted: true,
                                                }}
                                                className={clsx(
                                                    'grid size-12 place-items-center rounded-full bg-black ring-1 ring-white/10 sm:size-14',
                                                    'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                                    disableLike && 'opacity-40',
                                                )}
                                                preserveState
                                                onSuccess={() => {
                                                    console.log('test Like');
                                                    getClip();
                                                }}
                                            >
                                                <Heart
                                                    className={clsx(
                                                        'h-6 w-6 sm:h-7 sm:w-7',
                                                        isLiked
                                                            ? 'text-red-500'
                                                            : 'text-white',
                                                    )}
                                                />
                                            </Link>

                                            {/* Skip */}
                                            <Link
                                                type="button"
                                                aria-pressed={isSkipped}
                                                disabled={disableSkip}
                                                onClick={() => {
                                                    toggleSkip(it.id);
                                                }}
                                                href={store()}
                                                data={{
                                                    clip: it.id,
                                                    voted: false,
                                                }}
                                                className={clsx(
                                                    'grid size-12 place-items-center rounded-full bg-black ring-1 ring-white/10 sm:size-14',
                                                    'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                                    disableSkip && 'opacity-40',
                                                )}
                                                preserveState
                                                onSuccess={() => {
                                                    console.log('test Like');
                                                    getClip();
                                                }}
                                            >
                                                <CircleX
                                                    className={clsx(
                                                        'h-6 w-6 sm:h-7 sm:w-7',
                                                        isSkipped
                                                            ? 'text-red-500'
                                                            : 'text-white',
                                                    )}
                                                />
                                            </Link>

                                            {/* Next */}
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    scrollToIndex(index + 1)
                                                }
                                                disabled={
                                                    index === items.length - 1
                                                }
                                                className="grid size-12 place-items-center rounded-full bg-black/55 ring-1 ring-white/10 backdrop-blur transition-transform duration-150 ease-out active:scale-95 disabled:opacity-40 sm:size-14 sm:hover:scale-110"
                                            >
                                                <ChevronRight className="h-6 w-6 text-white" />
                                            </button>
                                        </div>
                                    </section>
                                );
                            })
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
