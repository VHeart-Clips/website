import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: AuthenticatedUser | null;
    permissions: String[] | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface AuthenticatedUser {
    id: number;
    name: string;
    email: string | null;
    email_verified_at: string | null;
    avatar?: string;
    clip_permission?: boolean;
    rules: string[];
    has_email_authentication: boolean;
    created_at: string;
    updated_at: string;
    broadcaster?: Broadcaster;
}

export interface FaqEntryResource {
    id: number;
    title: string;
    body: string;
    order: number;
}

export interface RoleUserListResource {
    id: number;
    name: string;
    weight: number;
    users: PublicUser[];
}

export interface RoleResource {
    id: number;
    name: string;
    description: string | null;
    weight: number;
    created_at: string;
    updated_at: string;
}
export type MinimalRoleResource = Pick<
    RoleResource | RoleUserListResource,
    'id' | 'name'
>;

export interface TagResource {
    id: number;
    name: string;
}

export interface CategoryResource {
    id: number;
    title: string;
    art: {
        small: string;
        medium: string;
        large: string;
    };
}

export interface PublicUser {
    id: number;
    name: string;
    avatar: string;
}

/* PublicClipResource */
export interface PublicClip {
    id: number;
    slug: string;
    title: string;
    thumbnail_url: string;
    clip_url: string;

    broadcaster?: PublicUser;
    clipper?: PublicUser;
    submitter?: PublicUser;
    category?: CategoryResource;
    vod?: [id: number, offset: number];
    votes?: number;
    clip_duration: number;
    clipped_at: string;
    submitted_at: string;
}

/** default Page Data for Dashboard */
export interface DashboardData extends SharedData {
    selectedStreamer: PublicUser;
    streamers: PublicUser[];
}

export interface Broadcaster {
    consent: number[];
    twitch_mod_permissions: number[];
    submit_user_allowed: boolean;
    submit_mods_allowed: boolean;
    submit_vip_allowed: boolean;
}
