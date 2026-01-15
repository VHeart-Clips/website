import ClipProcessCard from '@/components/aboutcard/clip-process-card';
import { AboutDonationCard as DonationCard } from '@/components/aboutcard/donation-card';
import HeroCard from '@/components/aboutcard/hero-card';
import VideoCard from '@/components/aboutcard/video-card';
import Footer from '@/components/footer/footer';
import SpaceBackground from '@/components/spacebackground';
import { Link } from '@inertiajs/react';
import { LogIn, Sparkles } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { login } from '@/routes';

export default function Welcome({
    donationUrl,
    partnerIcon,
    youtubeUrl,
}: {
    donationUrl?: string;
    partnerIcon?: string;
    youtubeUrl?: string;
}) {
    const { t } = useTranslation('welcome');
    return (
        <div className="relative flex min-h-screen flex-col overflow-hidden bg-blue-50 dark:bg-[#0a0a1a]">
            <SpaceBackground />

            <main className="relative z-10 flex flex-1 items-center justify-center px-4 py-12">
                <div className="w-full max-w-[1200px] space-y-8">
                    <div className="flex justify-end px-2">
                        <Link
                            href="/login"
                            className="group relative inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 hover:scale-105"
                        >
                            <span className="absolute -inset-1 rounded-full bg-gradient-to-r from-purple-600/25 to-cyan-500/20 opacity-60 blur-lg transition-opacity group-hover:opacity-90 dark:from-purple-600/35 dark:to-cyan-500/30" />
                            <span className="relative inline-flex items-center gap-2 rounded-full border border-purple-300/60 bg-gradient-to-r from-purple-100/90 to-cyan-100/80 px-5 py-2.5 text-purple-800 shadow-lg shadow-black/5 backdrop-blur-sm transition-all duration-300 group-hover:border-purple-400/70 group-hover:shadow-xl dark:border-purple-400/30 dark:bg-gradient-to-r dark:from-purple-500/20 dark:via-transparent dark:to-cyan-500/20 dark:text-white/90 dark:shadow-black/30 dark:group-hover:border-purple-400/50">
                                <span className="relative">
                                    <LogIn className="h-4 w-4" />
                                    <Sparkles className="absolute -top-1 -right-1 h-2 w-2 text-cyan-500 opacity-0 transition-opacity group-hover:opacity-100 dark:text-cyan-300" />
                                </span>
                                {t('login.members')}
                            </span>
                        </Link>
                    </div>

                    <HeroCard t={t} />
                    <DonationCard
                        t={t}
                        donationUrl={donationUrl}
                        partnerIcon={partnerIcon}
                    />
                    <VideoCard t={t} youtubeUrl={youtubeUrl} />
                    <ClipProcessCard t={t} />
                </div>
            </main>
        </div>
    );
}
