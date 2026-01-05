import AppLayout from '@/layouts/app-layout';
import { evaluateclips } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ChevronLeft, ChevronRight, Heart, CircleX } from 'lucide-react';
import clsx from 'clsx';

type Item = {
  id: number;
  clipSlug: string;
  title: string;
};

export default function EvaluateClips() {
  const { t } = useTranslation('evaluateclips');

  const breadcrumbs: BreadcrumbItem[] = [{ title: t('evaluateclips'), href: evaluateclips().url }];

  const items: Item[] = [
    { id: 1, clipSlug: 'StupidProtectiveScorpionTheRinger-rgF-JZJ3vPS2KKin', title: 'Clip 1' },
    { id: 2, clipSlug: 'SparklingCrunchyChoughNerfBlueBlaster-0eTNXxe7OLJpnyj_', title: 'Clip 2' },
    { id: 3, clipSlug: 'CourageousLazyBubbleteaStoneLightning-L4YWt7IyzpGD7wt7', title: 'Clip 3' },
  ];

  const [liked, setLiked] = useState<Set<number>>(new Set());
  const [activeIndex, setActiveIndex] = useState(0);
  const [skipped, setSkipped] = useState<Set<number>>(new Set());

  const containerRef = useRef<HTMLDivElement | null>(null);
  const itemRefs = useRef<(HTMLElement | null)[]>([]);

  function toggleLike(id: number) {
    setLiked((prev) => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });
  }

  function toggleSkip(id: number) {
    setSkipped((prev) => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
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
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (!best) return;
        const idx = Number((best.target as HTMLElement).dataset.index);
        if (!Number.isNaN(idx)) setActiveIndex(idx);
      },
      { root: containerRef.current, threshold: [0.6, 0.75, 0.9] }
    );

    itemRefs.current.forEach((el) => el && observer.observe(el));
    return () => observer.disconnect();
  }, []);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={t('page_title')} />

      <div className="relative mx-auto w-full max-w-3xl px-2 py-2">
        <header className="mb-4 text-center space-y-1">
          <h1 className="text-xl font-bold sm:text-2xl md:text-3xl">{t('headline')}</h1>
          <p className="mx-auto max-w-2xl text-sm sm:text-base text-muted-foreground">{t('subtitle')}</p>
        </header>

        <div className="relative overflow-hidden rounded-xl border bg-background shadow-sm dark:shadow-none dark:ring-1 dark:ring-white/10">
          <div
            ref={containerRef}
            className="h-[calc(100dvh-320px)] overflow-y-auto snap-y snap-mandatory overscroll-contain scrollbar-none"
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
                  className="h-[calc(100dvh-320px)] bg-black flex flex-col snap-start snap-always"
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
                      <div className="absolute inset-0 grid place-items-center text-white/40 text-sm">Clip bereit</div>
                    )}
                  </div>

                  {/* ACTION BAR */}
                  <div className="shrink-0 py-4 flex items-center justify-center gap-4">
                    {/* Previous */}
                    <button
                      type="button"
                      onClick={() => scrollToIndex(index - 1)}
                      disabled={index === 0}
                      className="grid size-12 sm:size-14 place-items-center rounded-full bg-black/55 backdrop-blur ring-1 ring-white/10 transition-transform duration-150 ease-out sm:hover:scale-110 active:scale-95 disabled:opacity-40"
                    >
                      <ChevronLeft className="h-6 w-6 text-white" />
                    </button>

                    {/* Like */}
                    <button
                      type="button"
                      aria-pressed={isLiked}
                      disabled={disableLike}
                      onClick={() =>toggleLike(it.id)}
                      className={clsx(
                        'grid size-12 sm:size-14 place-items-center rounded-full bg-black ring-1 ring-white/10',
                        'transition-transform duration-150 ease-out sm:hover:scale-110 active:scale-95',
                        disableLike && 'opacity-40'
                      )}
                    >
                      <Heart className={clsx('h-6 w-6 sm:h-7 sm:w-7', isLiked ? 'text-red-500' : 'text-white')} />
                    </button>

                    {/* Skip */}
                    <button
                      type="button"
                      aria-pressed={isSkipped}
                      disabled={disableSkip}
                      onClick={() => {
                                        toggleSkip(it.id);
                                        scrollToIndex(index+1);
                                    }}
                      className={clsx(
                        'grid size-12 sm:size-14 place-items-center rounded-full bg-black ring-1 ring-white/10',
                        'transition-transform duration-150 ease-out sm:hover:scale-110 active:scale-95',
                        disableSkip && 'opacity-40'
                      )}
                    >
                      <CircleX className={clsx('h-6 w-6 sm:h-7 sm:w-7', isSkipped ? 'text-red-500' : 'text-white')} />
                    </button>

                    {/* Next */}
                    <button
                      type="button"
                      onClick={() => scrollToIndex(index + 1)}
                      disabled={index === items.length - 1}
                      className="grid size-12 sm:size-14 place-items-center rounded-full bg-black/55 backdrop-blur ring-1 ring-white/10 transition-transform duration-150 ease-out sm:hover:scale-110 active:scale-95 disabled:opacity-40"
                    >
                      <ChevronRight className="h-6 w-6 text-white" />
                    </button>
                  </div>
                </section>
              );
            })}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
