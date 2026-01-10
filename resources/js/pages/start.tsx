import { TwitchClipContainer } from '@/components/TwitchClipContainer';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { start } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, CircleX, Heart, X } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function Start() {
    type Item = {
        id: number;
        clipSlug: string;
        title: string;
        thumbUrl: string;
    };

    const { t } = useTranslation('homepage');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Homepage'), href: start().url },
    ];

    // ⚠️ Für Twitch Embeds muss parent zum Host passen
    const twitchParent =
        typeof window !== 'undefined' ? window.location.hostname : 'localhost';

    const items: Item[] = [
        {
            id: 1,
            clipSlug: 'StupidProtectiveScorpionTheRinger-rgF-JZJ3vPS2KKin',
            title: 'Clip 1',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2025/12/07/16/31/carnation-10000623_1280.jpg',
        },
        {
            id: 2,
            clipSlug: 'SparklingCrunchyChoughNerfBlueBlaster-0eTNXxe7OLJpnyj_',
            title: 'Clip 2',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2017/05/08/13/15/spring-bird-2295434_1280.jpg',
        },
        {
            id: 3,
            clipSlug: 'CourageousLazyBubbleteaStoneLightning-L4YWt7IyzpGD7wt7',
            title: 'Clip 3',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2024/03/09/06/52/flowers-8622033_1280.jpg',
        },
        {
            id: 4,
            clipSlug: 'TacitImpartialWoodpeckerPRChase-NL8ZBxuZsia9_pLl',
            title: 'Clip 4',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2021/08/17/10/23/flowers-6552611_1280.jpg',
        },
        {
            id: 5,
            clipSlug:
                'TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy',
            title: 'Clip 5',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2025/12/07/16/31/carnation-10000623_1280.jpg',
        },
    ];

    // YouTube ID bleibt wie von dir gewünscht
    const latestVideoId = 'Cl1dyPcZo6s?si=9qvwR5Yo3USlaezr';

    // =========================================================
    // Loop Slider (3 sichtbar) – stabil ohne "hin und her"
    // =========================================================
    const bestRatedSliderRef = useRef<HTMLDivElement>(null);

    const baseCount = items.length;
    const loopItems = useMemo(() => [...items, ...items, ...items], [items]);

    // starte in der mittleren Kopie
    const [virtualIndex, setVirtualIndex] = useState(baseCount);

    // verhindert, dass der Reset-Scroll selbst wieder Resets triggert
    const isResettingRef = useRef(false);

    const getItemWidth = () => {
        const el = bestRatedSliderRef.current;
        if (!el) return 0;
        return el.clientWidth / 3;
    };

    const scrollToVirtualIndex = (idx: number, smooth: boolean) => {
        const el = bestRatedSliderRef.current;
        if (!el) return;

        const w = getItemWidth();
        el.scrollTo({ left: idx * w, behavior: smooth ? 'smooth' : 'auto' });
    };

    // initialer Jump in die mittlere Kopie (ohne Animation)
    useEffect(() => {
        scrollToVirtualIndex(baseCount, false);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Scroll-Ende erkennen (Debounce) und dann ggf. "unsichtbar" resetten
    useEffect(() => {
        const el = bestRatedSliderRef.current;
        if (!el) return;

        let t: number | undefined;

        const onScroll = () => {
            if (t) window.clearTimeout(t);

            t = window.setTimeout(() => {
                if (isResettingRef.current) return;

                const w = getItemWidth();
                if (!w) return;

                const idx = Math.round(el.scrollLeft / w);

                // mittlere Kopie: [baseCount .. 2*baseCount-1]
                if (idx >= 2 * baseCount) {
                    isResettingRef.current = true;
                    const resetIdx = idx - baseCount;

                    scrollToVirtualIndex(resetIdx, false);
                    setVirtualIndex(resetIdx);

                    requestAnimationFrame(() => {
                        isResettingRef.current = false;
                    });
                    return;
                }

                if (idx < baseCount) {
                    isResettingRef.current = true;
                    const resetIdx = idx + baseCount;

                    scrollToVirtualIndex(resetIdx, false);
                    setVirtualIndex(resetIdx);

                    requestAnimationFrame(() => {
                        isResettingRef.current = false;
                    });
                    return;
                }

                setVirtualIndex(idx);
            }, 120);
        };

        el.addEventListener('scroll', onScroll, { passive: true });
        return () => {
            el.removeEventListener('scroll', onScroll);
            if (t) window.clearTimeout(t);
        };
    }, [baseCount]);

    const next = () => {
        const nextIdx = virtualIndex + 1;
        setVirtualIndex(nextIdx);
        scrollToVirtualIndex(nextIdx, true);
    };

    const prev = () => {
        const prevIdx = virtualIndex - 1;
        setVirtualIndex(prevIdx);
        scrollToVirtualIndex(prevIdx, true);
    };

    // 3 sichtbar -> Mitte = virtualIndex + 1
    const isCenterIndex = (i: number) => i === virtualIndex + 1;

    // =========================================================
    // Modal + Like/Skip (gegenseitig deaktiviert)
    // =========================================================
    const [openClip, setOpenClip] = useState<Item | null>(null);

    const [liked, setLiked] = useState<Set<number>>(new Set());
    const [skipped, setSkipped] = useState<Set<number>>(new Set());

    const toggleLike = (id: number) => {
        if (skipped.has(id)) return;

        setLiked((prev) => {
            const nextSet = new Set(prev);
            nextSet.has(id) ? nextSet.delete(id) : nextSet.add(id);
            return nextSet;
        });
    };

    const toggleSkip = (id: number) => {
        if (liked.has(id)) return;

        setSkipped((prev) => {
            const nextSet = new Set(prev);
            nextSet.has(id) ? nextSet.delete(id) : nextSet.add(id);
            return nextSet;
        });
    };

    const openId = openClip?.id ?? -1;
    const isLiked = liked.has(openId);
    const isSkipped = skipped.has(openId);
    const disableLike = isSkipped;
    const disableSkip = isLiked;

    return (
        <AppHeaderLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <div className="flex flex-col gap-14">
                {/* Aktuellstes YouTube Video */}
                <section>
                    <h2 className="mb-4 py-5 text-center text-2xl font-bold">
                        AKTUELLSTES YOUTUBE VIDEO
                    </h2>
                    <div className="mx-auto aspect-video w-full max-w-4xl rounded-md shadow-md">
                        <iframe
                            src={`https://www.youtube.com/embed/${latestVideoId}`}
                            title="Aktuellstes YouTube Video"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                            className="h-full w-full"
                        />
                    </div>
                </section>

                {/* 2. Am besten bewertet */}
                <section>
                    <h2 className="mb-4 pt-5 text-center text-2xl font-semibold">
                        AM BESTEN BEWERTET DEZEMBER
                    </h2>

                    <div className="relative mx-auto w-11/12 max-w-7xl">
                        {/* Prev */}
                        <button
                            type="button"
                            onClick={prev}
                            className="absolute top-1/2 left-5 z-10 -translate-y-1/2 rounded-full bg-white/50 p-2 shadow transition-transform hover:scale-110 active:scale-95"
                            aria-label="Vorherige Clips"
                        >
                            <ChevronLeft className="h-8 w-8 text-black" />
                        </button>

                        {/* Slider Track */}
                        <div
                            ref={bestRatedSliderRef}
                            className="scrollbar-hide flex overflow-hidden scroll-smooth py-10"
                        >
                            {loopItems.map((it, index) => {
                                const isCenter = isCenterIndex(index);

                                return (
                                    <div
                                        key={`${it.clipSlug}-${index}`}
                                        className="w-1/3 flex-shrink-0 p-2"
                                    >
                                        <button
                                            type="button"
                                            onClick={() => setOpenClip(it)}
                                            aria-label={`Clip öffnen: ${it.title}`}
                                            className={`group relative aspect-video w-full overflow-hidden shadow-md transition-transform duration-300 ${
                                                isCenter
                                                    ? 'scale-x-110 hover:scale-125'
                                                    : 'scale-75 hover:scale-90'
                                            }`}
                                        >
                                            {it.thumbUrl ? (
                                                <img
                                                    src={it.thumbUrl}
                                                    alt={it.title}
                                                    className="h-full w-full object-cover"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="grid h-full w-full place-items-center bg-gray-400 text-sm text-white/80">
                                                    Lädt…
                                                </div>
                                            )}

                                            {/* overlay */}
                                            <div className="absolute inset-0 bg-black/0 transition-colors group-hover:bg-black/20" />

                                            {/* title */}
                                            <div className="absolute bottom-2 left-2 bg-black/60 px-2 py-1 text-xs text-white">
                                                {it.title}
                                            </div>
                                        </button>
                                    </div>
                                );
                            })}
                        </div>

                        {/* Next */}
                        <button
                            type="button"
                            onClick={next}
                            className="absolute top-1/2 right-5 z-10 -translate-y-1/2 rounded-full bg-white/50 p-2 shadow transition-transform hover:scale-110 active:scale-95"
                            aria-label="Nächste Clips"
                        >
                            <ChevronRight className="h-8 w-8 text-black" />
                        </button>
                    </div>
                </section>

                {/* 3. Eingereichte Clips */}
                <section>
                    <h2 className="mb-4 text-lg font-semibold">
                        EINGEREICHTE CLIPS
                    </h2>
                    <div className="grid grid-cols-2 gap-4 pb-10 md:grid-cols-4">
                        {[
                            'BENIS CLIP',
                            'TAWIS HASST UNS',
                            'SPEIDYS VERZWEIFLUNG',
                            'KATTY AM ENDE',
                            'CHAOS ABSOLUT BEWEGBILD',
                            'JustPlayer Hält es nicht mehr aus',
                            'Meyn hat das Wort Component gehört',
                            'Mir fällt nix ein',
                        ].map((title) => (
                            <div key={title} className="flex flex-col pb-10">
                                <div className="aspect-video h-full w-full rounded-md bg-gray-400 shadow-md" />
                                <span className="mt-2 text-center text-sm">
                                    {title}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Pop-up zum anschauen */}
                {openClip && (
                    <div
                        className="fixed inset-0 z-50 grid place-items-center bg-black/80 p-4"
                        onClick={() => setOpenClip(null)}
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
                                    onClick={() => setOpenClip(null)}
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
                                    onClick={() => toggleLike(openClip.id)}
                                    className={`grid size-14 place-items-center rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ${
                                        disableLike
                                            ? 'cursor-not-allowed opacity-40'
                                            : 'active:scale-95 sm:hover:scale-110'
                                    }`}
                                >
                                    <Heart
                                        className={`h-10 w-10 ${
                                            isLiked
                                                ? 'text-red-500'
                                                : 'text-white'
                                        }`}
                                    />
                                </button>

                                {/* Skip */}
                                <button
                                    type="button"
                                    aria-pressed={isSkipped}
                                    disabled={disableSkip}
                                    onClick={() => toggleSkip(openClip.id)}
                                    className={`grid size-14 place-items-center rounded-full bg-black ring-1 ring-white/10 transition-transform duration-150 ${
                                        disableSkip
                                            ? 'cursor-not-allowed opacity-40'
                                            : 'active:scale-95 sm:hover:scale-110'
                                    }`}
                                >
                                    <CircleX
                                        className={`h-10 w-10 ${
                                            isSkipped
                                                ? 'text-red-500'
                                                : 'text-white'
                                        }`}
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppHeaderLayout>
    );
}
