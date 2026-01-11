import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    sidebarContent?: ReactNode;
}

export default ({
    children,
    breadcrumbs,
    sidebarContent,
    ...props
}: AppLayoutProps) => (
    <AppLayoutTemplate
        breadcrumbs={breadcrumbs}
        sidebarContent={sidebarContent}
        {...props}
    >
        {children}
    </AppLayoutTemplate>
);
