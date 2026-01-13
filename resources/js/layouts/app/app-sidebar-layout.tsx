import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { AppTopbar } from '@/components/app-topbar';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren, type ReactNode } from 'react';

interface AppSidebarLayoutProps {
    breadcrumbs?: BreadcrumbItem[];
    /** Custom content for the sidebar (appears in sidebar header area) */
    sidebarContent?: ReactNode;
}

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
    sidebarContent,
}: PropsWithChildren<AppSidebarLayoutProps>) {
    return (
        <div className="flex min-h-screen flex-col">
            <AppTopbar />
            <div className="flex flex-1">
                <AppShell variant="sidebar">
                    <AppSidebar className="top-19! h-[calc(100svh-4.75rem)]!">
                        {sidebarContent}
                    </AppSidebar>
                    <AppContent variant="sidebar" className="overflow-x-hidden">
                        <AppSidebarHeader breadcrumbs={breadcrumbs} />
                        {children}
                    </AppContent>
                </AppShell>
            </div>
        </div>
    );
}
