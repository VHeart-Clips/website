import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { ChevronDown, Users } from 'lucide-react';
import { useTranslation } from 'react-i18next';

/**
 * Streamer selection section for the sidebar.
 * Shows a collapsible list of streamers the user manages.
 */
export function StreamerSection() {
    const { state } = useSidebar();
    const { t } = useTranslation('navigation');
    const isCollapsed = state === 'collapsed';

    return (
        <SidebarMenu>
            <Collapsible
                open={isCollapsed ? false : undefined}
                defaultOpen
                className="group/collapsible"
            >
                <SidebarMenuItem>
                    <CollapsibleTrigger asChild disabled={isCollapsed}>
                        <SidebarMenuButton className="hover:cursor-pointer hover:bg-transparent active:bg-transparent focus:bg-transparent data-[state=open]:bg-transparent data-[state=open]:hover:bg-transparent">
                            <Users className="size-4" />
                            <span className="font-medium">{t('streamer')}</span>
                            <ChevronDown className="ml-auto size-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenu className="pl-6 pt-1">
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
    );
}
