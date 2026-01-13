import { type BreadcrumbItem } from '@/types';
import { AppTopbar } from './app-topbar';

interface AppHeaderProps {
    breadcrumbs?: BreadcrumbItem[];
}

// eslint-disable-next-line no-empty-pattern
export function AppHeader({}: AppHeaderProps) {
    return <AppTopbar />;
}
