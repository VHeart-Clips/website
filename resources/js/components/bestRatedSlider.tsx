import { ClipModal } from '@/components/clipModal';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';

export type BestRatedItem = {
    id: number;
    clipSlug: string;
    title: string;
    thumbUrl: string;
};

export function BestRatedSlider({
    items,
    twitchParent,
    headline = 'AM BESTEN BEWERTET DEZEMBER',
}: {
    items: BestRatedItem[];
    twitchParent: string;
    headline?: string;
}) {
    const [openClip, setOpenClip] = useState<BestRatedItem | null>(null);
    const [liked, setLiked] = useState<Set<number>>(new Set());
    const [skipped, setSkipped] = useState<Set<number>>(new Set());

    const slidesToShow = 3;

    const [sliderRef, instanceRef] = useKeenSlider<HTMLDivElement>({
        loop: true,
        renderMode: 'performance',
        drag: false,
        slides: {
            perView: slidesToShow,
            spacing: 15,
        },
    });

    const next = () => instanceRef.current?.next();
    const prev = () => instanceRef.current?.prev();

    const toggleLike = (id: number) => {
        if (skipped.has(id)) return;

        setLiked((prev) => {
            const nextSet = new Set(prev);
            if (nextSet.has(id)) {
                nextSet.delete(id);
            } else {
                nextSet.add(id);
            }
            return nextSet;
        });
    };

    const toggleSkip = (id: number) => {
        if (liked.has(id)) return;

        setSkipped((prev) => {
            const nextSet = new Set(prev);
            if (nextSet.has(id)) {
                nextSet.delete(id);
            } else {
                nextSet.add(id);
            }
            return nextSet;
        });
    };

    const openId = openClip?.id ?? -1;
    const isLiked = liked.has(openId);
    const isSkipped = skipped.has(openId);
    const disableLike = isSkipped;
    const disableSkip = isLiked;

    if (!items.length) return null;

    return (
        <>
            <section>
                <h2 className="mb-4 pt-5 text-center text-2xl font-semibold">
                    {headline}
                </h2>

                <div className="relative mx-auto w-11/12">
                    {/* Prev */}
                    <button
                        type="button"
                        onClick={prev}
                        className="absolute top-1/2 left-1 z-10 -translate-y-1/2 rounded-full bg-white/50 p-2 shadow transition-transform hover:scale-110 active:scale-95"
                        aria-label="Vorherige Clips"
                    >
                        <ChevronLeft className="h-8 w-8 text-black" />
                    </button>

                    {/* Slider */}
                    <div ref={sliderRef} className="keen-slider py-15">
                        {items.map((it) => {
                            return (
                                <div
                                    key={it.clipSlug}
                                    className="keen-slider__slide"
                                >
                                    <button
                                        type="button"
                                        onClick={() => setOpenClip(it)}
                                        aria-label={`Clip öffnen: ${it.title}`}
                                        className={`group relative aspect-video w-full overflow-hidden rounded-xl shadow-md transition-transform duration-300 dark:bg-gradient-to-b dark:from-white/50 dark:to-black/40 dark:ring-1 dark:ring-white/10`}
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

                                        <div className="text-md absolute bottom-2 left-2 rounded-xl bg-black/60 px-2 py-1 text-white">
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
                        className="absolute top-1/2 right-1 z-10 -translate-y-1/2 rounded-full bg-white/50 p-2 shadow transition-transform hover:scale-110 active:scale-95"
                        aria-label="Nächste Clips"
                    >
                        <ChevronRight className="h-8 w-8 text-black" />
                    </button>
                </div>
            </section>

            {openClip && (
                <ClipModal
                    openClip={openClip}
                    twitchParent={twitchParent}
                    isLiked={isLiked}
                    isSkipped={isSkipped}
                    disableLike={disableLike}
                    disableSkip={disableSkip}
                    onClose={() => setOpenClip(null)}
                    onToggleLike={toggleLike}
                    onToggleSkip={toggleSkip}
                />
            )}
        </>
    );
}
