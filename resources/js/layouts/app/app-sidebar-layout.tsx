import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { AppTopbar } from '@/components/app-topbar';
import Footer from '@/components/footer/footer';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren, type ReactNode } from 'react';

interface AppSidebarLayoutProps {
    breadcrumbs?: BreadcrumbItem[];
    /** Custom content for the sidebar*/
    sidebarContent?: ReactNode;
}

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
    sidebarContent,
}: PropsWithChildren<AppSidebarLayoutProps>) {
    return (
        <div className="flex min-h-screen flex-col">
            <AppTopbar isIsland={false} />
            <div className="flex flex-1">
                <AppShell variant="sidebar">
                    <AppSidebar className="top-16! h-[calc(100svh-4.75rem-var(--app-banner-height,0px))]! transition-[top,height] duration-300 ease-out sm:top-18!">
                        {sidebarContent}
                    </AppSidebar>
                    <AppContent variant="sidebar" className="mt-2! ml-2!">
                        <AppSidebarHeader breadcrumbs={breadcrumbs} />
                        {children}
                    </AppContent>
                    <Footer isIsland={false} />
                </AppShell>
            </div>
        </div>
    );
}
