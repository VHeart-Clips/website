import { ClipPreview } from '@/components/clip-preview';
import { ClipModal } from '@/components/clipModal';
import StaticSpaceBackground from '@/components/spacebackground';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { clips, main } from '@/routes/dashboard';
import { PublicClip, PublicUser, type BreadcrumbItem } from '@/types';
import {
    Combobox,
    ComboboxButton,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
} from '@headlessui/react';
import { Head, InfiniteScroll, Link, usePage } from '@inertiajs/react';
import clsx from 'clsx';
import {
    CheckIcon,
    ChevronDownIcon,
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

    const status = [
        { id: 0, name: 'Unknown' },
        { id: 1, name: 'NeedApproval' },
        { id: 2, name: 'Approved' },
        { id: 3, name: 'Blocked' },
    ];

    const [selectedStatus, setSelectedStatus] = useState([
        status[0],
        status[1],
    ]);

    return (
        <AppLayout breadcrumbs={breadcrumbs} sidebarVariant="creator_dashboard">
            <Head title={props.selectedStreamer.name + ' Dashboard Clips'} />
            <StaticSpaceBackground />
            <div className="gap-4 rounded-xl p-4">
                <div className="sticky top-19 z-10 mb-5 inline-flex w-full justify-start gap-4 rounded-2xl border border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 p-2 ring-black/5 dark:border-white/20 dark:bg-black/80 dark:bg-none! dark:from-transparent! dark:via-transparent! dark:to-transparent!">
                    <DropdownMenu>
                        <DropdownMenuTrigger>
                            <Button variant="outline">Filter</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-50" align="start">
                            <DropdownMenuGroup>
                                <DropdownMenuLabel>Status</DropdownMenuLabel>
                                <Select>
                                    <SelectTrigger className="w-full max-w-48">
                                        <SelectValue placeholder="Select a Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="0">
                                                Unknown
                                            </SelectItem>
                                            <SelectItem value="1">
                                                Need Approval
                                            </SelectItem>
                                            <SelectItem value="2">
                                                Approved
                                            </SelectItem>
                                            <SelectItem value="3">
                                                Blocked
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </DropdownMenuGroup>
                            <DropdownMenuGroup>
                                <DropdownMenuLabel>Category</DropdownMenuLabel>
                                <Select>
                                    <SelectTrigger className="w-full max-w-48">
                                        <SelectValue placeholder="Select a Category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="12345678901">
                                                Just Chatting
                                            </SelectItem>
                                            <SelectItem value="12345678902">
                                                Minecraft
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </DropdownMenuGroup>
                            <DropdownMenuGroup>
                                <DropdownMenuLabel>Clipper</DropdownMenuLabel>
                                <Select>
                                    <SelectTrigger className="w-full max-w-48">
                                        <SelectValue placeholder="Select a Clipper" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="12345678901">
                                                Clipper A
                                            </SelectItem>
                                            <SelectItem value="12345678902">
                                                Clipper B
                                            </SelectItem>
                                            <SelectItem value="12345678903">
                                                Clipper C
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </DropdownMenuGroup>
                            <DropdownMenuGroup>
                                <DropdownMenuLabel>Submitter</DropdownMenuLabel>
                                <Select>
                                    <SelectTrigger className="w-full max-w-48">
                                        <SelectValue placeholder="Select a Submitter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="12345678901">
                                                Submitter A
                                            </SelectItem>
                                            <SelectItem value="12345678902">
                                                Submitter B
                                            </SelectItem>
                                            <SelectItem value="12345678903">
                                                Submitter C
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </DropdownMenuGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Select>
                        <SelectTrigger className="w-full max-w-48">
                            <SelectValue placeholder="Select a Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="0">Unknown</SelectItem>
                                <SelectItem value="1">Need Approval</SelectItem>
                                <SelectItem value="2">Approved</SelectItem>
                                <SelectItem value="3">Blocked</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <Combobox
                        multiple
                        value={selectedStatus}
                        onChange={setSelectedStatus}
                    >
                        <div className="relative w-full max-w-xs">
                            <span className="inline-block w-full rounded-md border border-input p-2">
                                <div className="relative w-full">
                                    <span className="flex flex-wrap gap-1">
                                        {selectedStatus.map((stati) => (
                                            <Badge
                                                key={stati.id}
                                                variant="outline"
                                                className="text-input"
                                            >
                                                {stati.name}
                                            </Badge>
                                        ))}
                                        <ComboboxInput
                                            aria-label="Input"
                                            className="border-none p-0"
                                        />
                                    </span>
                                    <ComboboxButton className="absolute inset-y-0 right-0 flex items-center px-2">
                                        <ChevronDownIcon className="size-4 fill-white/60 group-data-hover:fill-white" />
                                    </ComboboxButton>
                                </div>
                            </span>
                            <ComboboxOptions
                                anchor="bottom"
                                transition
                                className={clsx(
                                    'absolute rounded-xl border border-white/5 bg-white/5 p-1 empty:invisible',
                                    'opacity-100 transition duration-100 ease-in data-leave:data-closed:opacity-0',
                                )}
                            >
                                {status.map((stati) => (
                                    <ComboboxOption
                                        key={stati.id}
                                        value={stati}
                                        className="group flex cursor-default items-center gap-2 rounded-lg px-3 py-1.5 select-none data-focus:bg-white/10"
                                    >
                                        <CheckIcon className="invisible size-4 fill-white group-data-selected:visible" />
                                        <div className="text-sm/6 text-white">
                                            {stati.name}
                                        </div>
                                    </ComboboxOption>
                                ))}
                            </ComboboxOptions>
                        </div>
                    </Combobox>
                </div>
                <InfiniteScroll data="clips" preserveUrl buffer={150}>
                    <div className="max-grid mb-17 grid auto-rows-min gap-4 overscroll-contain">
                        {props.clips?.data?.map((clip) => (
                            <div
                                key={'clip' + clip.id}
                                className="relative grid h-32 grid-cols-6 grid-rows-1 gap-6 rounded-2xl border border-gray-200 bg-linear-to-br from-white/70 via-white/85 to-white/70 p-2 ring-black/5 dark:border-white/20 dark:bg-black/80 dark:bg-none! dark:from-transparent! dark:via-transparent! dark:to-transparent!"
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
                    </div>
                </InfiniteScroll>
            </div>
            {openClip && (
                <ClipModal clip={openClip} onClose={() => setOpenClip(null)} />
            )}
        </AppLayout>
    );
}
