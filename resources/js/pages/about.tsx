import ClipProcessCard from '@/components/aboutcard/clip-process-card';
import { AboutDonationCard as DonationCard } from '@/components/aboutcard/donation-card';
import HeroCard from '@/components/aboutcard/hero-card';
import SpaceBackground from '@/components/spacebackground';
import { useTranslation } from 'react-i18next';

export default function About({
    donationUrl,
    partnerIcon,
}: {
    donationUrl?: string;
    partnerIcon?: string;
}) {
    const { t } = useTranslation('about');

    return (
            <div className="relative flex min-h-screen flex-col overflow-hidden bg-blue-50 dark:bg-[#0a0a1a]">
                <SpaceBackground />

                <main className="relative z-10 flex flex-1 items-center justify-center px-4 py-12">
                    <div className="w-full max-w-[1200px] space-y-8">
                        <HeroCard t={t} />
                        <DonationCard
                            t={t}
                            donationUrl={donationUrl}
                            partnerIcon={partnerIcon}
                        />
                        <ClipProcessCard t={t} />
                    </div>
                </main>
            </div>
    );
}
