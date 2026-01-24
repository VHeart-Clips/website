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
import { dashboard, evaluateclips, start } from '@/routes';
import submitclip from '@/routes/submitclip';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, LayoutGrid, ScanHeart, Search, Send } from 'lucide-react';
import { lazy, Suspense, useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import LogoFullDark from '/resources/images/svg/logo-full-dark.svg';
import LogoFullLight from '/resources/images/svg/logo-full-title.svg';

const TwitchPermissionsBanner = lazy(
    () => import('@/components/twitch-permissions-banner'),
);

// Navigation item keys for translation lookup
const navItemKeys = [
    {
        key: 'dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        key: 'submit_clips',
        href: submitclip.create(),
        icon: Send,
    },
    {
        key: 'evaluate_clips',
        href: ClipVoteController.create(),
        icon: ScanHeart,
    },
] as const;

export function AppTopbar() {
    const { t } = useTranslation('navigation');
    const page = usePage<SharedData>();
    const { auth } = page.props;
    const getInitials = useInitials();
    const searchInputRef = useRef<HTMLInputElement>(null);

    // Keyboard shortcut: Ctrl+K to focus search
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInputRef.current?.focus();
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, []);

    // Check if current URL matches href
    const isActive = (href: string | { url: string }) => {
        const hrefString = typeof href === 'string' ? href : href.url;
        return page.url === hrefString || page.url.startsWith(hrefString + '/');
    };

    const showTwitchPermissionsBanner = Boolean(
        page.flash?.showTwitchPermissionsPrompt,
    );

    return (
        <div className="sticky top-0 z-50 w-full">
            {showTwitchPermissionsBanner && (
                <Suspense fallback={null}>
                    <TwitchPermissionsBanner />
                </Suspense>
            )}
            <header className="w-full px-2 py-2">
                <div className="flex h-14 items-center gap-4 rounded-xl bg-background px-4 shadow-xl">
                {/* Logo */}
                <div className="flex w-auto shrink-0 items-center md:w-[calc(var(--sidebar-width)-3.5rem)]">
                    <Link
                        href={start()}
                        prefetch
                        className="flex items-center transition-opacity hover:opacity-80"
                    >
                        <img
                            src={LogoFullDark}
                            alt={t('logo_alt')}
                            className="hidden h-8 dark:block"
                        />
                        <img
                            src={LogoFullLight}
                            alt={t('logo_alt')}
                            className="block h-8 dark:hidden"
                        />
                    </Link>
                </div>

                {/* Search Field */}
                <div className="flex flex-1 px-30">
                    <div className="relative w-full max-w-md">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground hover:text-accent" />
                        <input
                            ref={searchInputRef}
                            type="text"
                            placeholder={t('search_placeholder')}
                            className="h-9 w-full rounded-lg border border-sidebar-border bg-background pr-12 pl-9 text-sm text-sidebar-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        />
                        <kbd className="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2 rounded border border-sidebar-border bg-background px-1.5 py-0.5 text-xs font-medium text-muted-foreground">
                            {t('search_shortcut')}
                        </kbd>
                    </div>
                </div>

                {/* Navigation Links */}
                <nav className="flex items-center gap-1">
                    {navItemKeys.map((item) => (
                        <Link
                            key={item.key}
                            href={item.href}
                            className={cn(
                                'flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                                isActive(item.href)
                                    ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                    : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground',
                            )}
                        >
                            {item.icon && <item.icon className="size-4" />}
                            <span className="hidden lg:inline">
                                {t(item.key)}
                            </span>
                        </Link>
                    ))}
                </nav>

                {/* User Dropdown */}
                <div className="flex shrink-0 items-center">
                    <DropdownMenu modal={false}>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                className="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-sidebar-accent/50"
                            >
                                <Avatar className="size-7 overflow-hidden rounded-full">
                                    <AvatarImage
                                        src={auth.user.avatar}
                                        alt={auth.user.name}
                                    />
                                    <AvatarFallback className="rounded-full bg-neutral-200 text-xs text-black dark:bg-neutral-700 dark:text-white">
                                        {getInitials(auth.user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <span className="hidden text-sm font-medium lg:inline">
                                    {auth.user.name}
                                </span>
                                <ChevronDown className="size-4 text-muted-foreground" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56" align="end">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                </div>
            </header>
        </div>
    );
}
