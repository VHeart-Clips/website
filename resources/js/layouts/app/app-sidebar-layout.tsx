import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { AppTopbar } from '@/components/app-topbar';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    return (
        <div className="flex min-h-screen flex-col">
            <AppTopbar />
            <div className="flex flex-1">
                <AppShell variant="sidebar">
                    <AppSidebar className="!top-[4.75rem] !h-[calc(100svh-4.75rem)]" />
                    <AppContent variant="sidebar" className="overflow-x-hidden">
                        <AppSidebarHeader breadcrumbs={breadcrumbs} />
                        {children}
                    </AppContent>
                </AppShell>
            </div>
        </div>
    );
}
