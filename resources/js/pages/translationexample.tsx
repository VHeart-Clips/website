import { useTranslation } from 'react-i18next';

export default function Welcome() {
    const { t } = useTranslation(['auth']);

    return (
        <>
            <p>{t('throttle', { seconds: Math.random() })}</p>
        </>
    );
}
