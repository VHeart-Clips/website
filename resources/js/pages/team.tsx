import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { team } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import cat from '/resources/images/png/cat.png';

interface TeamMember {
    name: string;
    avatar?: string;
    description?: string;
}

interface TeamRole {
    name: string;
    members: TeamMember[];
}

interface TeamPageProps {
    roles: TeamRole[];
}

function getInitials(name: string): string {
    const cleaned = String(name).trim();
    if (!cleaned) return '?';

    const parts = cleaned.split(/\s+/);
    const first = parts[0]?.[0] || '';
    const second = parts[1]?.[0] || parts[0]?.[1] || '';

    return (first + second).toUpperCase() || '?';
}

const ROLE_COLORS = {
    Admin: { bg: 'bg-red-100', text: 'text-red-800', border: 'border-red-200' },
    'Community Manager': {
        bg: 'bg-blue-100',
        text: 'text-blue-800',
        border: 'border-blue-200',
    },
    Mod: {
        bg: 'bg-green-100',
        text: 'text-green-800',
        border: 'border-green-200',
    },
    'Jr Mod': {
        bg: 'bg-yellow-100',
        text: 'text-yellow-800',
        border: 'border-yellow-200',
    },
    Cutter: {
        bg: 'bg-purple-100',
        text: 'text-purple-800',
        border: 'border-purple-200',
    },
    'IT-Management': {
        bg: 'bg-indigo-100',
        text: 'text-indigo-800',
        border: 'border-indigo-200',
    },
    'Dev/Tec': {
        bg: 'bg-indigo-100',
        text: 'text-indigo-800',
        border: 'border-indigo-200',
    },
} as const;

function TeamMemberCard({
    member,
    roleName,
}: {
    member: TeamMember;
    roleName: string;
}) {
    const { t } = useTranslation('team');
    const { name, avatar } = member;

    const avatarSrc = avatar?.trim() || cat;
    const avatarAlt = t('avatar_alt', {
        name,
        defaultValue: `${name}'s profile picture`,
    });
    //todo: Member Desc ... maybe vom backend oder raus?
    //const memberDescription = t(`members.${desc}`, { defaultValue: name });

    return (
        <div
            className="group flex items-center gap-3 rounded-lg border p-3 transition-all duration-200 hover:bg-muted/50 hover:shadow-sm"
            role="listitem"
            aria-label={t('team_member', {
                name,
                role: roleName,
                defaultValue: `${name}, ${roleName}`,
            })}
        >
            <Avatar className="h-12 w-12 ring-2 ring-background group-hover:ring-primary/20">
                <AvatarImage
                    src={avatarSrc}
                    alt={avatarAlt}
                    loading="lazy"
                    className="object-cover"
                />
                <AvatarFallback className="bg-primary/10">
                    {getInitials(name)}
                </AvatarFallback>
            </Avatar>

            <div className="min-w-0 flex-1">
                <p className="truncate font-semibold text-foreground group-hover:text-primary">
                    {name}
                </p>
                {/*<p*/}
            {/*        className="truncate text-xs text-muted-foreground"*/}
            {/*        title={memberDescription}*/}
            {/*    >*/}
            {/*        {memberDescription}*/}
            {/*    </p>*/}
            </div>
        </div>
    );
}

function RoleSection({ role }: { role: TeamRole }) {
    const { t } = useTranslation('team');
    const { name, members } = role;

    const roleColor = ROLE_COLORS[name as keyof typeof ROLE_COLORS] || {
        bg: 'bg-slate-100',
        text: 'text-slate-800',
        border: 'border-slate-200',
    };

    const translatedRoleName = t(`roles.${name}`, { defaultValue: name });
    const memberCount = members.length;
    const memberLabel = memberCount === 1 ? t('member') : t('members');

    return (
        <section
            className="space-y-6"
            aria-labelledby={`role-${name.replace(/\s+/g, '-').toLowerCase()}`}
        >
            <div className="flex flex-wrap items-center gap-3">
                <Badge
                    variant="outline"
                    className={`${roleColor.bg} ${roleColor.text} ${roleColor.border} px-4 py-1.5 font-medium`}
                >
                    {translatedRoleName}
                </Badge>

                <span className="text-sm text-muted-foreground">
                    {memberCount} {memberLabel}
                </span>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {members.map((member, index) => (
                    <TeamMemberCard
                        key={`${name}-${member.name}-${index}`}
                        member={member}
                        roleName={translatedRoleName}
                    />
                ))}
            </div>
        </section>
    );
}

export default function TeamPage({ roles = [] }: TeamPageProps) {
    const { t } = useTranslation('team');

    const breadcrumbs: BreadcrumbItem[] = useMemo(
        () => [{ title: t('breadcrumb'), href: team().url }],
        [t],
    );

    const totalMembers = useMemo(
        () =>
            roles.reduce(
                (total, role) => total + (role.members?.length || 0),
                0,
            ),
        [roles],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <div className="min-h-screen bg-gradient-to-b from-background via-background to-secondary/5">
                <div className="container mx-auto px-4 py-8 sm:py-12">
                    <header className="mb-10 text-center">
                        <h1 className="mb-3 text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl">
                            {t('our_team', 'Unser Team')}
                        </h1>
                            <span className="mt-2 block text-sm font-medium text-primary">
                                {t('total_members', {
                                    count: totalMembers,
                                    defaultValue: `${totalMembers} Teammitglieder`,
                                })}
                            </span>
                    </header>

                    <main>
                        <Card className="overflow-hidden border shadow-sm">
                            <CardContent className="p-6 sm:p-8">
                                <div className="space-y-10">
                                    {roles.length > 0 ? (
                                        roles.map((role) => (
                                            <RoleSection
                                                key={role.name}
                                                role={role}
                                            />
                                        ))
                                    ) : (
                                        <div
                                            className="rounded-lg border-2 border-dashed p-8 text-center"
                                            role="alert"
                                            aria-live="polite"
                                        >
                                            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                                <span className="text-2xl">
                                                    👥
                                                </span>
                                            </div>
                                            <h3 className="mb-2 text-lg font-semibold">
                                                {t(
                                                    'no_team_data',
                                                    'Noch kein Team vorhanden',
                                                )}
                                            </h3>
                                            <p className="text-muted-foreground">
                                                {t(
                                                    'no_team_description',
                                                    'Das Team wird in Kürze hier erscheinen.',
                                                )}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </main>
                </div>
            </div>
        </AppLayout>
    );
}
