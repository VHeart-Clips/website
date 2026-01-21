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
import { ClipPreview } from '@/components/clip-preview';
import { ClipModal } from '@/components/clipModal';
import SpaceBackground from '@/components/spacebackground';

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
            title: 'Gutes Angebot!',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/StupidProtectiveScorpionTheRinger-rgF-JZJ3vPS2KKin/be6e56b1-76fe-4953-a68a-f3011d82db7b/preview.jpg',
            likes: 135,
            lengthSeconds: 27,
            broadcasterName: 'JaxOffTV',
        },
        {
            id: 2,
            clipSlug: 'SparklingCrunchyChoughNerfBlueBlaster-0eTNXxe7OLJpnyj_',
            title: 'HAHA GOT EM!',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/SparklingCrunchyChoughNerfBlueBlaster-0eTNXxe7OLJpnyj_/902004d7-09bd-4512-89b7-f84fa7b09b3a/preview.jpg',
            likes: 186,
            lengthSeconds: 60,
            broadcasterName: 'JaxOffTV',
        },
        {
            id: 3,
            clipSlug: 'CourageousLazyBubbleteaStoneLightning-L4YWt7IyzpGD7wt7',
            title: 'Es gibt wirklich nichts besseres!',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/CourageousLazyBubbleteaStoneLightning-L4YWt7IyzpGD7wt7/cb751787-75e4-430d-9375-99157987d20b/preview.jpg',
            likes: 174,
            lengthSeconds: 6,
            broadcasterName: 'JaxOffTV',
        },
        {
            id: 4,
            clipSlug: 'TacitImpartialWoodpeckerPRChase-NL8ZBxuZsia9_pLl',
            title: 'Traumfrau right here!',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/TacitImpartialWoodpeckerPRChase-NL8ZBxuZsia9_pLl/ac1d090c-bbce-4b5f-b0a9-fb5922fb4cca/preview.jpg',
            likes: 250,
            lengthSeconds: 10,
            broadcasterName: 'JaxOffTV',
        },
        {
            id: 5,
            clipSlug:
                'TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy',
            title: 'ICH LIEBE FRAUEN MIT SCHWANZ',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/TemperedHedonisticStingrayCharlietheUnicorn-hpb9Rc1YOv-7xNQy/bf267526-2ce6-4d6d-a037-0cfda6d1d315/preview.jpg',
            likes: 999,
            lengthSeconds: 7,
            broadcasterName: 'JaxOffTV',
        },
    ];

    const latestVideoId = 'D9PHIxhU_MM?si=L69fuJNqLNI8y0Jc';

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
            title: 'Warum glaubt ihr mir das nicht..',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/PunchyPeppyGoatDoritosChip-bP9qRVBqcGZwRgvS/8a763c18-18ab-4e5c-8e03-f9981b33c9e6/preview.jpg',
            clipSlug: 'PunchyPeppyGoatDoritosChip-bP9qRVBqcGZwRgvS',
            likes: 93,
            lengthSeconds: 54,
            broadcasterName: 'YuraYami',
        },
        {
            id: 2,
            title: 'doch nicht vain aber vain >:c',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/LazyWittyPicklesBleedPurple-jbBQkK6TuheD79NF/4bfa3bf8-c02f-4ac6-b26f-b77ed00ba348/preview.jpg',
            clipSlug: 'LazyWittyPicklesBleedPurple-jbBQkK6TuheD79NF',
            likes: 82,
            lengthSeconds: 30,
            broadcasterName: 'SleepyTawi',
        },
        {
            id: 3,
            title: 'LKW müde LKW Schlafen',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips/PnsL2BswvFhK4SXteh-rmg/AT-cm%7CPnsL2BswvFhK4SXteh-rmg-preview-260x147.jpg',
            clipSlug: 'ObliqueBeautifulCookieKappaRoss-GzC8WErhJe16H6ua',
            likes: 56,
            lengthSeconds: 29,
            broadcasterName: 'Speidy674',
        },
        {
            id: 4,
            title: 'Yura macht komische geräusche beim Ersschrecken',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips/m_eyKBEReJdVFHTRnYxWVQ/AT-cm%7Cm_eyKBEReJdVFHTRnYxWVQ-preview-260x147.jpg',
            clipSlug: 'IntelligentExpensiveAppleKappaWealth-ERF4l1UUdNGdjFZV',
            likes: 219,
            lengthSeconds: 18,
            broadcasterName: 'YuraYami',
        },
        {
            id: 5,
            title: 'Warum bin ich tot?',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/FlirtyBillowingBaguetteFeelsBadMan-bxCYVssRKWe6wDYy/27663e35-b3a4-4856-9e7e-92c54d65b0f2/preview.jpg',
            clipSlug: 'FlirtyBillowingBaguetteFeelsBadMan-bxCYVssRKWe6wDYy',
            likes: 41,
            lengthSeconds: 40,
            broadcasterName: 'YuraYami',
        },
        {
            id: 6,
            title: 'SkillOffTV OwO',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips-thumbnails-prod/InexpensiveSaltyLegWoofer-ZSa1SKMF7rrQqdLG/e0f73986-504a-4963-9119-22e41b1684f5/preview.jpg',
            clipSlug: 'InexpensiveSaltyLegWoofer-ZSa1SKMF7rrQqdLG',
            likes: 18,
            lengthSeconds: 23,
            broadcasterName: 'JaxOffTV',
        },
        {
            id: 7,
            title: 'Schaf aus dem Ofen?',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips/YliMyLUpVdjUqvKBb3MloQ/AT-cm%7CYliMyLUpVdjUqvKBb3MloQ-preview-260x147.jpg',
            clipSlug: 'CoweringCuriousWoodcockStoneLightning-tSlV2NSq8oFvcyWh',
            likes: 69,
            lengthSeconds: 18,
            broadcasterName: 'Speidy674',
        },
        {
            id: 8,
            title: 'Der Kjekel hat keine Bremesen',
            thumbUrl:
                'https://static-cdn.jtvnw.net/twitch-clips/LPOsLW36_bX95H5QLzYXIQ/AT-cm%7CLPOsLW36_bX95H5QLzYXIQ-preview-260x147.jpg',
            clipSlug: 'NiceMildPeanutRedCoat-bfr9tf3kflAvNhch',
            likes: 61,
            lengthSeconds: 22,
            broadcasterName: 'Speidy674',
        },
    ];

    return (
        <AppHeaderLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />
            <SpaceBackground />
            <div className="relative z-10 mx-auto w-[90vw] py-5">
                <Card className="mx-auto rounded-2xl border border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 p-8 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:bg-none! dark:from-transparent! dark:via-transparent! dark:to-transparent! dark:ring-0 dark:shadow-purple-900/30">
                    <CardContent>
                        <div className="flex flex-col gap-14">
                            <section>
                                <h2 className="mb-4 py-5 text-center text-2xl font-bold">
                                    AKTUELLSTES YOUTUBE VIDEO
                                </h2>

                                <div className="mx-auto aspect-video w-full max-w-4xl overflow-hidden rounded-xl dark:bg-linear-to-b dark:from-white/10 dark:to-black/40 dark:ring-1 dark:ring-white/10">
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

                                <div className="grid grid-cols-2 gap-4 pb-0 md:grid-cols-4">
                                    {discover_items.map((it) => (
                                        <div
                                            key={it.id}
                                            className="overflow-hidden rounded-md transition-transform hover:scale-105"
                                        >
                                            <ClipPreview
                                                thumbUrl={it.thumbUrl}
                                                title={it.title}
                                                likes={it.likes}
                                                lengthSeconds={it.lengthSeconds}
                                                broadcasterName={
                                                    it.broadcasterName
                                                }
                                                onClick={() => setOpenClip(it)}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </section>

                            <section className="mt-0 flex justify-center pb-5">
                                <button
                                    type="button"
                                    className="rounded-full bg-primary/85 px-6 py-2 text-sm font-medium text-white shadow-md transition hover:scale-105 hover:bg-primary active:scale-105"
                                >
                                    Mehr entdecken
                                </button>
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
