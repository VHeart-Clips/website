import ClipVoteController from '@/actions/App/Http/Controllers/ClipVoteController';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { dashboard } from '@/routes';
import submitclip from '@/routes/submitclip';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/react';
import { LayoutGrid, ScanHeart, Send, type LucideIcon } from 'lucide-react';
import { useMemo } from 'react';

// Navigation items for icon lookup - defined outside component
const navItemIcons: { href: string | { url: string }; icon: LucideIcon }[] = [
    {
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        href: submitclip.create(),
        icon: Send,
    },
    {
        href: ClipVoteController.create(),
        icon: ScanHeart,
    },
];

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const page = usePage();

    // Find the current page's icon - memoized to avoid recreating during render
    const Icon = useMemo(() => {
        const currentNavItem = navItemIcons.find((item) => {
            const hrefString =
                typeof item.href === 'string' ? item.href : item.href.url;
            return (
                page.url === hrefString || page.url.startsWith(hrefString + '/')
            );
        });
        return currentNavItem?.icon ?? LayoutGrid;
    }, [page.url]);

    return (
        <header className="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 px-6 md:px-4">
            <div className="flex items-center gap-2">
                <Icon className="size-5 text-muted-foreground" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
        </header>
    );
}
