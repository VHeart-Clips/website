import { Link, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    LayoutGrid,
    LucideProps,
    ScanHeart,
    Search,
    Send,
    X,
} from 'lucide-react';
import {
    ForwardRefExoticComponent,
    lazy,
    RefAttributes,
    Suspense,
    useEffect,
    useRef,
    useState,
} from 'react';
import { useTranslation } from 'react-i18next';

import ClipVoteController from '@/actions/App/Http/Controllers/ClipVoteController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import { dashboard, home, login } from '@/routes';
import submitclip from '@/routes/submitclip';
import { type SharedData as BaseSharedData } from '@/types';

import LogoFullDark from '/resources/images/svg/logo-full-dark.svg';
import LogoFullLight from '/resources/images/svg/logo-full-title.svg';
import { RouteDefinition } from '@/wayfinder';

interface SharedData extends BaseSharedData {
    flash?: {
        showTwitchPermissionsPrompt?: boolean;
    };
}

interface NavigationItem {
    key: string;
    href: string | RouteDefinition<'get'>;
    icon: ForwardRefExoticComponent<
        Omit<LucideProps, 'ref'> & RefAttributes<SVGSVGElement>
    >;
    static?: boolean;
}

interface AppTopbarProps {
    isIsland?: boolean;
}

const TwitchPermissionsBanner = lazy(
    () => import('@/components/twitch-permissions-banner'),
);

const NAVIGATION_ITEMS = [
    { key: 'dashboard', href: dashboard(), icon: LayoutGrid },
    {
        key: 'submit_clips',
        href: submitclip.create().url,
        icon: Send,
        static: true,
    },
    {
        key: 'evaluate_clips',
        href: ClipVoteController.create(),
        icon: ScanHeart,
    },
] as NavigationItem[];

export function AppTopbar({ isIsland = true }: AppTopbarProps) {
    const { t } = useTranslation('navigation');
    const page = usePage<SharedData>();
    const auth = page.props.auth || { user: null };
    const getInitials = useInitials();
    const desktopSearchRef = useRef<HTMLInputElement>(null);
    const mobileSearchRef = useRef<HTMLInputElement>(null);
    const [isMobileSearchActive, setIsMobileSearchActive] = useState(false);

    const checkActive = (href: string | { url: string }) => {
        const path = typeof href === 'string' ? href : href.url;
        return page.url === path || page.url.startsWith(`${path}/`);
    };

    const activateSearch = () => {
        if (window.innerWidth < 768) {
            setIsMobileSearchActive(true);
            setTimeout(() => mobileSearchRef.current?.focus(), 50);
        } else {
            desktopSearchRef.current?.focus();
        }
    };

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
                event.preventDefault();
                activateSearch();
            }
        };
        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, []);

    return (
        <div
            className={cn(
                'sticky top-0 z-50 w-full',
                isIsland ? 'px-2 pt-2 sm:px-4 sm:pt-4' : 'px-0 pt-0',
            )}
        >
            {Boolean(page.props.flash?.showTwitchPermissionsPrompt) && (
                <div className={cn('w-full', isIsland ? 'mb-2' : 'mb-0')}>
                    <Suspense fallback={null}>
                        <TwitchPermissionsBanner />
                    </Suspense>
                </div>
            )}

            <header
                className={cn(
                    'relative flex h-14 w-full items-center justify-between',
                    'bg-gradient-to-br from-white/70 via-white/85 to-white/70 text-gray-900 backdrop-blur-md',
                    'dark:bg-black/80 dark:!bg-none dark:text-white/85',
                    isIsland
                        ? [
                              'mx-auto rounded-2xl border border-gray-200 px-3 shadow-xl ring-1 ring-black/5 sm:px-4',
                              'dark:border-white/20 dark:ring-0',
                          ]
                        : [
                              'h-18 rounded-none border-b border-gray-200 px-4 shadow-none sm:px-6 md:px-8',
                              'dark:border-white/20',
                          ],
                )}
            >
                <div
                    className={cn(
                        'absolute inset-0 z-20 flex items-center gap-2 bg-background/95 px-3 md:hidden',
                        !isMobileSearchActive && 'hidden',
                        isIsland ? 'rounded-2xl' : 'rounded-none',
                    )}
                >
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-accent" />
                        <input
                            ref={mobileSearchRef}
                            type="text"
                            placeholder={t('search_placeholder')}
                            onBlur={() =>
                                !mobileSearchRef.current?.value &&
                                setIsMobileSearchActive(false)
                            }
                            className="h-9 w-full rounded-xl border-none bg-black/5 pr-4 pl-9 text-sm focus:ring-2 focus:ring-accent dark:bg-white/10"
                        />
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => setIsMobileSearchActive(false)}
                        className="shrink-0 focus:bg-accent focus:text-accent-foreground"
                    >
                        <X className="size-5" />
                    </Button>
                </div>

                <div className="flex min-w-0 flex-1 items-center justify-start">
                    <Link
                        href={home()}
                        className="flex items-center hover:opacity-80"
                    >
                        <img
                            src={LogoFullDark}
                            alt={t('logo_alt')}
                            className="hidden h-6 sm:h-8 dark:block"
                        />
                        <img
                            src={LogoFullLight}
                            alt={t('logo_alt')}
                            className="block h-6 sm:h-8 dark:hidden"
                        />
                    </Link>
                </div>

                <div className="hidden flex-none items-center justify-center md:flex md:px-4">
                    <div className="group relative w-full md:w-[320px] lg:w-[450px]">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground group-focus-within:text-accent" />
                        <input
                            ref={desktopSearchRef}
                            type="text"
                            placeholder={t('search_placeholder')}
                            className="h-9 w-full rounded-xl border-none bg-black/5 pr-12 pl-9 text-sm focus:ring-2 focus:ring-accent dark:bg-white/10"
                        />
                        <kbd className="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2 rounded border border-gray-300/50 bg-white/50 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground dark:border-white/10 dark:bg-black/20">
                            {t('search_shortcut')}
                        </kbd>
                    </div>
                </div>

                <div className="flex flex-1 items-center justify-end gap-0.5 sm:gap-2">
                    {auth.user && (
                        <nav className="flex items-center gap-0.5 sm:gap-1">
                            {NAVIGATION_ITEMS.map((item) => {
                                if (item?.static) {
                                    return (
                                        <a
                                            key={item.key}
                                            href={String(item.href)}
                                            className={cn(
                                                'relative flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium outline-hidden select-none sm:px-3',
                                                checkActive(item.href)
                                                    ? 'bg-accent text-accent-foreground'
                                                    : 'text-gray-600 hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground dark:text-white/70 dark:hover:text-white dark:focus:text-white',
                                            )}
                                        >
                                            <item.icon className="size-5 sm:size-4" />
                                            <span className="hidden lg:inline">
                                                {t(item.key)}
                                            </span>
                                        </a>
                                    );
                                }

                                return (
                                    <Link
                                        key={item.key}
                                        href={item.href}
                                        className={cn(
                                            'relative flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium outline-hidden select-none sm:px-3',
                                            checkActive(item.href)
                                                ? 'bg-accent text-accent-foreground'
                                                : 'text-gray-600 hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground dark:text-white/70 dark:hover:text-white dark:focus:text-white',
                                        )}
                                    >
                                        <item.icon className="size-5 sm:size-4" />
                                        <span className="hidden lg:inline">
                                            {t(item.key)}
                                        </span>
                                    </Link>
                                );
                            })}
                        </nav>
                    )}

                    <Button
                        variant="ghost"
                        size="icon"
                        className="flex hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground md:hidden"
                        onClick={activateSearch}
                    >
                        <Search className="size-5" />
                    </Button>

                    <div className="flex items-center">
                        {auth.user ? (
                            <DropdownMenu modal={false}>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        className="h-auto gap-2 rounded-xl px-1 py-1 text-gray-600 hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground sm:px-2 sm:py-1.5 dark:text-white/70 dark:hover:text-white"
                                    >
                                        <Avatar className="size-7 border border-gray-200 dark:border-white/10">
                                            <AvatarImage
                                                src={auth.user.avatar}
                                                alt={auth.user.name}
                                            />
                                            <AvatarFallback className="bg-neutral-200 text-[10px] text-black dark:bg-neutral-700 dark:text-white">
                                                {getInitials(auth.user.name)}
                                            </AvatarFallback>
                                        </Avatar>

                                        <span className="hidden text-sm font-medium xl:inline">
                                            {auth.user.name}
                                        </span>
                                        <ChevronDown className="hidden size-4 opacity-70 lg:block" />
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent
                                    className="mt-2 w-56 rounded-xl border border-gray-200 bg-white/95 shadow-2xl backdrop-blur-lg dark:border-white/10 dark:bg-black/90"
                                    align="end"
                                >
                                    <UserMenuContent user={auth.user} />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <Link href={login()}>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="rounded-xl border-gray-200 text-gray-600 hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground dark:border-white/20 dark:text-white/70 dark:hover:text-white"
                                >
                                    {t('login')}
                                </Button>
                            </Link>
                        )}
                    </div>
                </div>
            </header>
        </div>
    );
}

export default AppTopbar;
