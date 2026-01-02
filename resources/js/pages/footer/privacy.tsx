import { useTranslation } from 'react-i18next';

export default function PrivacyPage() {
    const { t } = useTranslation('footer');

    return (
        <main className="container mx-auto px-4 py-12">
            <article className="mx-auto max-w-3xl">
                <header className="mb-8">
                    <h1 className="text-3xl font-semibold">
                        {t('privacy.title')}
                    </h1>
                    <p className="mt-2 text-white/70">{t('privacy.short')}</p>
                </header>
            </article>
        </main>
    );
}
