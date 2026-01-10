import SpaceBackground from '@/components/spacebackground';
import { Card, CardContent } from '@/components/ui/card';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { start } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

import {
    BestRatedSlider,
    type BestRatedItem,
} from '@/components/bestRatedSlider';
import { ClipModal } from '@/components/clipModal';

export default function Start() {
    const { t } = useTranslation('homepage');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Homepage'), href: start().url },
    ];

    const twitchParent = 'localhost';

    const items: BestRatedItem[] = [
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
                'https://cdn.pixabay.com/photo/2023/03/09/07/04/bird-7839371_960_720.jpg',
        },
    ];

    const latestVideoId = '9o8qf7anq0g?si=oQ6hTJ08-pGrdG8e';

    const [openClip, setOpenClip] = useState<BestRatedItem | null>(null);
    const [liked, setLiked] = useState<Set<number>>(new Set());
    const [skipped, setSkipped] = useState<Set<number>>(new Set());

    const toggleLike = (id: number) => {
        if (skipped.has(id)) return;
        setLiked((prev) => {
            const nextSet = new Set(prev);
            if (nextSet.has(id)) nextSet.delete(id);
            else nextSet.add(id);
            return nextSet;
        });
    };

    const toggleSkip = (id: number) => {
        if (liked.has(id)) return;
        setSkipped((prev) => {
            const nextSet = new Set(prev);
            if (nextSet.has(id)) nextSet.delete(id);
            else nextSet.add(id);
            return nextSet;
        });
    };

    const openId = openClip?.id ?? -1;
    const isLiked = liked.has(openId);
    const isSkipped = skipped.has(openId);
    const disableLike = isSkipped;
    const disableSkip = isLiked;

    const discover_items: BestRatedItem[] = [
        {
            id: 1,
            title: 'BENIS CLIP',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2014/10/26/14/36/light-bulb-503881_1280.jpg',
            clipSlug: 'StupidProtectiveScorpionTheRinger-rgF-JZJ3vPS2KKin',
        },
        {
            id: 2,
            title: 'TAWI HASST UNS',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2018/01/16/07/42/time-3085468_1280.jpg',
            clipSlug: 'SparklingCrunchyChoughNerfBlueBlaster-0eTNXxe7OLJpnyj_',
        },
        {
            id: 3,
            title: 'SPEIDYS VERZWEIFLUNG',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2021/11/30/13/21/vintage-camera-6835351_1280.jpg',
            clipSlug: 'CourageousLazyBubbleteaStoneLightning-L4YWt7IyzpGD7wt7',
        },
        {
            id: 4,
            title: 'KATTY AM ENDE',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2015/10/22/17/28/stack-of-books-1001655_1280.jpg',
            clipSlug: 'TacitImpartialWoodpeckerPRChase-NL8ZBxuZsia9_pLl',
        },
        {
            id: 5,
            title: 'CHAOS ABSOLUT BEWEGBILD',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2018/01/18/20/43/literature-3091212_1280.jpg',
            clipSlug:
                'TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy',
        },
        {
            id: 6,
            title: 'Justin hält es nicht mehr aus',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2015/07/17/22/42/library-849797_1280.jpg',
            clipSlug:
                'TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy',
        },
        {
            id: 7,
            title: 'Meyn hat das Wort Component gehört',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2022/03/06/10/23/books-7051311_1280.jpg',
            clipSlug:
                'TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy',
        },
        {
            id: 8,
            title: 'Mir fällt nix ein',
            thumbUrl:
                'https://cdn.pixabay.com/photo/2016/01/27/04/32/books-1163695_1280.jpg',
            clipSlug:
                'TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy',
        },
    ];

    return (
        <AppHeaderLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />
            <SpaceBackground />

            <div className="relative z-10 mx-auto w-11/12 max-w-6xl py-5">
                <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-8 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
                    <CardContent>
                        <div className="flex flex-col gap-14">
                            <section>
                                <h2 className="mb-4 py-5 text-center text-2xl font-bold">
                                    AKTUELLSTES YOUTUBE VIDEO
                                </h2>

                                <div className="mx-auto aspect-video w-full max-w-4xl overflow-hidden rounded-xl shadow-md dark:bg-gradient-to-b dark:from-white/10 dark:to-black/40 dark:ring-1 dark:ring-white/10">
                                    <iframe
                                        src={`https://www.youtube.com/embed/${latestVideoId}`}
                                        title="Aktuellstes YouTube Video"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowFullScreen
                                        className="h-full w-full"
                                    />
                                </div>
                            </section>

                            <BestRatedSlider
                                items={items}
                                twitchParent={twitchParent}
                                headline="AM BESTEN BEWERTET DEZEMBER"
                            />

                            <section>
                                <h2 className="mb-4 text-lg font-semibold">
                                    EINGEREICHTE CLIPS
                                </h2>

                                <div className="grid grid-cols-2 gap-4 pb-10 md:grid-cols-4">
                                    {discover_items.map((it) => (
                                        <div
                                            key={it.id}
                                            className="flex flex-col overflow-hidden rounded-md pb-10 transition-transform hover:scale-105"
                                        >
                                            <button
                                                type="button"
                                                onClick={() => setOpenClip(it)}
                                                aria-label={`Clip öffnen: ${it.title}`}
                                                className="aspect-video h-full w-full overflow-hidden rounded-md bg-gray-400 shadow-md"
                                            >
                                                <img
                                                    src={it.thumbUrl}
                                                    alt={it.title}
                                                    className="h-full w-full object-cover"
                                                    loading="lazy"
                                                />
                                            </button>

                                            <span className="mt-2 text-center text-sm">
                                                {it.title}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </section>
                        </div>
                    </CardContent>
                </Card>
            </div>

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
        </AppHeaderLayout>
    );
}
