import AppLayout from '@/layouts/app-layout';
import { about } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import Logo from '/resources/images/svg/logo-full-title.svg';

export default function About() {
    const { t } = useTranslation('about');

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('breadcrumb'),
            href: about().url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <div className="container mx-auto max-w-2xl px-4 py-12 sm:px-6">
                <div className="mb-8 flex justify-center">
                    <img src={Logo} alt="Logo" className="h-32 w-auto" />
                </div>

                <div className="text-center">
                    <p className="mb-6 text-lg text-muted-foreground">
                        {t('hero.subtitle')}
                    </p>

                    <div className="space-y-4 text-foreground">
                        <p>{t('hero.description')}</p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
