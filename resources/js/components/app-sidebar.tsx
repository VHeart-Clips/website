import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { about, team } from '@/routes';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder } from 'lucide-react';
import { type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

// Footer navigation item keys for translation lookup
const footerNavItemKeys = [
    {
        key: 'team',
        href: team(),
        icon: Folder,
    },
    {
        key: 'about',
        href: about(),
        icon: BookOpen,
    },
] as const;

interface AppSidebarProps {
    className?: string;
    children?: ReactNode;
}

export function AppSidebar({ className, children }: AppSidebarProps) {
    const { t } = useTranslation('navigation');

    return (
        <Sidebar collapsible="icon" variant="inset" className={className}>
            {/* Sidebar Header - receives custom content via children */}
            {children && <SidebarHeader>{children}</SidebarHeader>}

            {/* Main content area - empty for now, can be extended later */}
            <SidebarContent />

            {/* Footer navigation */}
            <SidebarFooter>
                <SidebarMenu className="mt-auto">
                    {footerNavItemKeys.map((item) => (
                        <SidebarMenuItem key={`footer-${item.key}`}>
                            <SidebarMenuButton asChild>
                                <Link
                                    href={item.href}
                                    preserveScroll
                                    preserveState
                                    only={[]}
                                    className="flex items-center gap-2"
                                >
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    <span>{t(item.key)}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ))}
                </SidebarMenu>

                {/* Sidebar collapse toggle */}
                <SidebarTrigger />
            </SidebarFooter>
        </Sidebar>
    );
}
