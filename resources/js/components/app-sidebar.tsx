import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
    useSidebar,
} from '@/components/ui/sidebar';
import { about, team } from '@/routes';
import { Link } from '@inertiajs/react';
import { BookOpen, ChevronDown, Folder, Users } from 'lucide-react';
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
}

export function AppSidebar({ className }: AppSidebarProps) {
    const { state } = useSidebar();
    const { t } = useTranslation('navigation');
    const isCollapsed = state === 'collapsed';

    return (
        <Sidebar collapsible="icon" variant="inset" className={className}>
            {/* Streamer Accordion */}
            <SidebarHeader>
                <SidebarMenu>
                    <Collapsible
                        open={isCollapsed ? false : undefined}
                        defaultOpen
                        className="group/collapsible"
                    >
                        <SidebarMenuItem>
                            <CollapsibleTrigger asChild disabled={isCollapsed}>
                                <SidebarMenuButton className="hover:cursor-pointer hover:bg-transparent focus:bg-transparent active:bg-transparent data-[state=open]:bg-transparent data-[state=open]:hover:bg-transparent">
                                    <Users className="size-4" />
                                    <span className="font-medium">
                                        {t('streamer')}
                                    </span>
                                    <ChevronDown className="ml-auto size-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenu className="pt-1 pl-6">
                                    <SidebarMenuItem>
                                        <SidebarMenuButton
                                            size="sm"
                                            className="text-muted-foreground hover:bg-transparent active:bg-transparent"
                                        >
                                            <span className="text-xs italic">
                                                {t('no_streamers_yet')}
                                            </span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                </SidebarMenu>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </SidebarMenu>
            </SidebarHeader>

            {/* Main content area - empty for now */}
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
