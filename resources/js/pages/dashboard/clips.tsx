import { ClipPreview } from '@/components/clip-preview';
import { ClipModal } from '@/components/clipModal';
import StaticSpaceBackground from '@/components/spacebackground';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { clips, main } from '@/routes/dashboard';
import { PublicClip, PublicUser, type BreadcrumbItem } from '@/types';
import { Head, InfiniteScroll, Link, usePage } from '@inertiajs/react';
import clsx from 'clsx';
import {
    CheckIcon,
    SettingsIcon,
    TrashIcon,
    TriangleAlertIcon,
} from 'lucide-react';
import { useState } from 'react';

type PageProps = {
    selectedStreamer: PublicUser;
    clips?: {
        data: PublicClip[];
    };
};

export default function DashboardClips() {
    const { props } = usePage<PageProps>();
    const [openClip, setOpenClip] = useState<PublicClip | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: main(props.selectedStreamer.id).url,
        },
        {
            title: 'Clips',
            href: clips(props.selectedStreamer.id).url,
        },
    ];

    console.log(props.clips?.data);

    return (
        <AppLayout breadcrumbs={breadcrumbs} sidebarVariant="creator_dashboard">
            <Head title={props.selectedStreamer.name + ' Dashboard Clips'} />
            <StaticSpaceBackground />
            <div className="sticky top-0">
                <Button>Filter</Button>
            </div>
            <div className="h-full gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4">
                    <InfiniteScroll data="clips" preserveUrl buffer={50}>
                        {props.clips?.data?.map((clip) => (
                            <div
                                key={'clip' + clip.id}
                                className="relative grid h-32 grid-cols-6 grid-rows-1 gap-6 rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-2 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent"
                            >
                                <div
                                    key={'clip' + clip.id}
                                    className="aspect-video h-full overflow-hidden rounded-md"
                                >
                                    <ClipPreview
                                        clip={clip}
                                        hideTitle
                                        onClick={() => setOpenClip(clip)}
                                    />
                                </div>
                                <div className="col-span-4 grid grid-cols-4 grid-rows-1 gap-4">
                                    <div className="content-center">
                                        <p>{clip.title}</p>
                                        <p>{clip.category?.title}</p>
                                    </div>

                                    <div className="content-center">
                                        <p>Tags</p>
                                        {clip.tags?.map((tag) => (
                                            <p>{tag.name}</p>
                                        ))}
                                    </div>

                                    <div className="content-center">
                                        <p>Clipper</p>
                                        <p>{clip.clipper?.name}</p>
                                        <p>{clip.clipped_at}</p>
                                    </div>

                                    <div className="content-center">
                                        <p>Submitter</p>
                                        <p>{clip.submitter?.name}</p>
                                        <p>{clip.submitted_at}</p>
                                    </div>
                                </div>
                                <div className="col-start-6 grid w-50 grid-flow-col grid-rows-2 content-center">
                                    <Link
                                        key={'clip.' + clip.id + '.btn1'}
                                        disabled
                                        type="button"
                                        className={clsx(
                                            'grid size-2 place-items-center rounded-full bg-black ring-1 ring-white/10 disabled:cursor-not-allowed disabled:opacity-50 sm:size-11',
                                            'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                        )}
                                        preserveState
                                    >
                                        <CheckIcon className="text-lime-500" />
                                    </Link>
                                    <Link
                                        key={'clip.' + clip.id + '.btn2'}
                                        disabled
                                        type="button"
                                        className={clsx(
                                            'grid size-2 place-items-center rounded-full bg-black ring-1 ring-white/10 disabled:cursor-not-allowed disabled:opacity-50 sm:size-11',
                                            'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                        )}
                                        preserveState
                                    >
                                        <TriangleAlertIcon className="text-yellow-500" />
                                    </Link>
                                    <Link
                                        key={'clip.' + clip.id + '.btn3'}
                                        disabled
                                        type="button"
                                        className={clsx(
                                            'grid size-2 place-items-center rounded-full bg-black ring-1 ring-white/10 disabled:cursor-not-allowed disabled:opacity-50 sm:size-11',
                                            'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                        )}
                                        preserveState
                                    >
                                        <TrashIcon className="text-red-500" />
                                    </Link>
                                    <Link
                                        key={'clip.' + clip.id + '.btn4'}
                                        disabled
                                        type="button"
                                        className={clsx(
                                            'grid size-2 place-items-center rounded-full bg-black ring-1 ring-white/10 disabled:cursor-not-allowed disabled:opacity-50 sm:size-11',
                                            'transition-transform duration-150 ease-out active:scale-95 sm:hover:scale-110',
                                        )}
                                        preserveState
                                    >
                                        <SettingsIcon className="text-white" />
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </InfiniteScroll>
                </div>
            </div>
            {openClip && (
                <ClipModal clip={openClip} onClose={() => setOpenClip(null)} />
            )}
        </AppLayout>
    );
}
