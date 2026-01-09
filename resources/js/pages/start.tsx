import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { start } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Start() {
    const { t } = useTranslation('homepage');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Homepage'), href: start().url },
    ];

    return (
        <AppHeaderLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <p>bla-bla-bla</p>
        </AppHeaderLayout>
    );
}
