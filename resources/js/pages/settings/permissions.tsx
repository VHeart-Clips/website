import HeadingSmall from '@/components/heading-small';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit, update } from '@/routes/permissions';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Permissions',
        href: edit().url,
    },
];

export default function Permissions() {
    const { t } = useTranslation('settings');
    const { auth } = usePage<SharedData>().props;
    const [clipPermission, setClipPermission] = useState(
        Boolean(auth.user?.clip_permission),
    );
    const [isUpdating, setIsUpdating] = useState(false);

    const granted = Boolean(clipPermission);

    const handleChange = (checked: boolean | 'indeterminate') => {
        const value = checked === true;
        setClipPermission(value);
        setIsUpdating(true);
        router.patch(
            update().url,
            { clip_permission: value },
            {
                preserveScroll: true,
                onError: () =>
                    setClipPermission(Boolean(auth.user?.clip_permission)),
                onFinish: () => setIsUpdating(false),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('permissions.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={t('permissions.title')}
                        description={t('permissions.description')}
                    />

                    <div className="space-y-4 rounded-lg border border-border/60 p-4">
                        <div className="flex items-start justify-between gap-4">
                            <div className="space-y-1">
                                <p className="text-sm font-medium">
                                    {t('permissions.clip_title')}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {t('permissions.clip_description')}
                                </p>
                            </div>
                            <Badge variant={granted ? 'default' : 'secondary'}>
                                {granted
                                    ? t('permissions.granted')
                                    : t('permissions.revoked')}
                            </Badge>
                        </div>

                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="clip_permission"
                                checked={granted}
                                onCheckedChange={handleChange}
                                disabled={isUpdating}
                            />
                            <Label htmlFor="clip_permission">
                                {t('permissions.toggle_label')}
                            </Label>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {t('permissions.clip_disclaimer')}
                        </p>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
