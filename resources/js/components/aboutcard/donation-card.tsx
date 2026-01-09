import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Heart, Shield, Users } from 'lucide-react';

interface AboutDonationCardProps {
    t: (key: string) => string;
    donationUrl?: string;
    partnerIcon?: string;
}

export function AboutDonationCard({
    t,
    donationUrl,
    partnerIcon,
}: AboutDonationCardProps) {
    return (
        <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-8 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
            <div className="px-6 py-8 sm:px-10 sm:py-12">
                <div className="mx-auto max-w-5xl">
                    <div className="mb-10 grid gap-8 lg:grid-cols-2">
                        <div className="space-y-6">
                            <div className="flex items-center gap-3">
                                <div className="rounded-xl border border-gray-300/80 bg-white/60 p-2.5 dark:border-white/20 dark:bg-black/20">
                                    <Users className="h-6 w-6 text-gray-900/90 dark:text-white" />
                                </div>
                                <h2 className="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-2xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                    {t('about.title')}
                                </h2>
                            </div>

                            <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                {t('about.p1')}
                            </p>

                            <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                {t('about.p2')}
                            </p>

                            <div className="mt-4 rounded-xl border border-gray-300/80 bg-white/60 p-4 dark:border-white/15 dark:bg-black/20">
                                <p className="text-sm font-bold text-gray-900/90 dark:text-white/90">
                                    {t('about.hashtag')}
                                </p>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-300/80 bg-white/60 p-6 dark:border-white/15 dark:bg-black/20">
                            <div className="mb-4 flex items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                    <img
                                        src={partnerIcon}
                                        alt="logo"
                                        className="h-12 w-12 object-contain"
                                    />
                                </div>

                                <div>
                                    <h3 className="mb-2 bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-2xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                        {t('donation.title')}
                                    </h3>
                                    <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                        {t('donation.intro')}
                                    </p>
                                </div>
                            </div>

                            <div className="mb-6 space-y-4">
                                <div className="rounded-lg bg-purple-50/80 p-4 dark:bg-purple-900/20">
                                    <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                        <span className="font-bold">
                                            {t('donation.hashtag')}
                                        </span>
                                    </p>
                                </div>

                                <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                    {t(
                                        'donation.partner_p1',
                                        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                                        // @ts-expect-error
                                        {
                                            partner: t(
                                                'donation.partner_placeholder',
                                            ),
                                        },
                                    )}
                                </p>

                                <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                    {t('donation.partner_p2')}
                                </p>

                                <div className="mt-4 rounded-lg bg-cyan-50/80 p-4 dark:bg-cyan-900/20">
                                    <p className="text-center text-sm leading-relaxed font-bold text-gray-800 dark:text-white/90">
                                        {t('donation.banner')}
                                    </p>
                                </div>
                            </div>

                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-center gap-2 text-xs text-gray-700 dark:text-white/70">
                                    <Shield className="h-3 w-3" />
                                    <span>{t('donation.trust_line')}</span>
                                </div>
                                {donationUrl ? (
                                    <a
                                        href={donationUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-block"
                                    >
                                        <Button
                                            size="lg"
                                            className="rounded-full border-0 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400 px-8 py-5 font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-emerald-600 hover:via-teal-500 hover:to-cyan-500 hover:shadow-xl hover:shadow-emerald-500/25"
                                        >
                                            <Heart className="mr-2 h-5 w-5" />
                                            {t('donation.cta')}
                                        </Button>
                                    </a>
                                ) : (
                                    <Button
                                        size="lg"
                                        className="rounded-full border-0 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400 px-8 py-5 font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-emerald-600 hover:via-teal-500 hover:to-cyan-500 hover:shadow-xl hover:shadow-emerald-500/25"
                                    >
                                        <Heart className="mr-2 h-5 w-5" />
                                        {t('donation.cta')}
                                    </Button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
