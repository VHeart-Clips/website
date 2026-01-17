import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import TextLink from '@/components/text-link';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { InfoIcon, XIcon } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import AppBanner from '@/components/app-banner';
import { edit as editPermissions } from '@/routes/permissions';

export default function TwitchPermissionsBanner() {
    const { t } = useTranslation('twitch');
    const { flash } = usePage<SharedData>();
    const [dismissed, setDismissed] = useState(false);

    const shouldShow = Boolean(flash?.showTwitchPermissionsPrompt);

    return (
        <AppBanner visible={shouldShow && !dismissed} offsetVariable="--app-banner-height">
            <div className="px-6 py-3">
                <Alert className="flex items-start justify-between gap-4 border-transparent bg-[#ba185d] text-white">
                    <div className="flex items-start gap-3">
                        <InfoIcon size={21} className="shrink-0" />
                        <div>
                            <AlertTitle className="text-white">{t('permissions_prompt.title')}</AlertTitle>
                            <AlertDescription className="text-white/90">
                                <p>{t('permissions_prompt.description')}</p>
                                <TextLink
                                    href={editPermissions().url}
                                    className="mt-1 text-white underline decoration-white/60"
                                >
                                    {t('permissions_prompt.settings_link')}
                                </TextLink>
                            </AlertDescription>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => setDismissed(true)}
                        aria-label={t('permissions_prompt.dismiss')}
                    >
                        <XIcon />
                    </Button>
                </Alert>
            </div>
        </AppBanner>
    );
}
