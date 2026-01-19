import Footer from '@/components/footer/footer';
import SpaceBackground from '@/components/spacebackground';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from 'react-i18next';
import Logo from '/resources/images/svg/logo-dark.svg';
import LogoLight from '/resources/images/svg/logo-light.svg';

export default function Welcome({
    kannRegistrieren = true,
}: {
    kannRegistrieren?: boolean;
}) {
    const { t } = useTranslation('login');

    return (
        <div className="relative flex min-h-screen flex-col overflow-hidden bg-blue-50 dark:bg-[#0a0a1a]">
            <SpaceBackground />

            <div
                className="fixed inset-0 bg-gradient-to-t from-[#C9D3E7]/65 via-[#DDE4F1]/40 to-[#C9D3E7]/55 dark:from-[#0a0a1a]/90 dark:via-transparent dark:to-[#0a0a1a]/80"
                style={{ zIndex: -40 }}
            />

            <div
                className="fixed inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.16)_0%,transparent_55%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.14)_0%,transparent_55%)] dark:bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.15)_0%,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.1)_0%,transparent_50%)]"
                style={{ zIndex: -39 }}
            />

            <main className="relative z-10 flex flex-1 flex-col items-center justify-center p-4 pb-16">
                <Card className="w-full max-w-md border-black/10 bg-gradient-to-br from-white/55 via-white/70 to-white/55 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-gradient-to-br dark:from-black/40 dark:via-black/30 dark:to-black/40 dark:shadow-2xl dark:shadow-purple-900/30">
                    <CardHeader className="space-y-6 text-center">
                        <div className="flex justify-center">
                            <div className="relative">
                                <img
                                    src={LogoLight}
                                    alt={t('logo_alt')}
                                    className="h-24 w-24 drop-shadow-[0_0_30px_rgba(145,70,255,0.22)] dark:hidden"
                                />
                                <img
                                    src={Logo}
                                    alt={t('logo_alt')}
                                    className="hidden h-24 w-24 drop-shadow-[0_0_40px_rgba(145,70,255,0.7)] dark:block"
                                />
                                <div className="absolute inset-0 rounded-full bg-purple-500/14 blur-2xl dark:bg-purple-500/30" />
                                <div className="absolute -inset-4 animate-pulse rounded-full border-2 border-purple-500/14 dark:border-purple-500/20" />
                            </div>
                        </div>

                        <CardTitle className="text-4xl font-bold tracking-tight">
                            <span className="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                {t('title')}
                            </span>
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        <p className="text-center text-lg leading-relaxed text-gray-900/85 dark:text-white/90">
                            {t('description')}
                        </p>

                        <div className="flex justify-center">
                            <div className="h-[1px] w-32 bg-gradient-to-r from-transparent via-gray-900/35 to-transparent dark:via-white/50" />
                        </div>
                    </CardContent>

                    <CardFooter className="flex flex-col space-y-6">
                        {kannRegistrieren && (
                            <a
                                href='/auth/twitch'
                                className="group relative w-full"
                                aria-label={t('connect_button_aria')}
                            >
                                <div className="absolute -inset-1 rounded-lg bg-gradient-to-r from-purple-600 to-cyan-500 opacity-40 blur-xl transition-opacity duration-300 group-hover:opacity-60 dark:opacity-60" />
                                <Button
                                    className="relative w-full border-0 bg-gradient-to-r from-purple-600 via-purple-500 to-cyan-500 py-7 text-lg shadow-2xl transition-all duration-300 group-hover:shadow-black/15 hover:from-purple-700 hover:to-cyan-600 dark:from-purple-700 dark:via-purple-600 dark:to-cyan-600 dark:group-hover:shadow-purple-500/30 dark:hover:from-purple-800 dark:hover:to-cyan-700"
                                    size="lg"
                                >
                                    <div className="flex items-center justify-center space-x-3">
                                        <div className="relative">
                                            <TwitchIcon className="h-7 w-7 text-white" />
                                            <div className="absolute inset-0 bg-cyan-500/22 blur-md dark:bg-cyan-400/40" />
                                        </div>
                                        <span className="font-bold text-white drop-shadow-lg">
                                            {t('connect_button')}
                                        </span>
                                    </div>
                                </Button>
                            </a>
                        )}

                        <p className="border-t border-black/10 pt-4 text-center text-sm text-gray-800/70 dark:border-white/20 dark:text-white/70">
                            {t('terms_notice')}
                        </p>
                    </CardFooter>
                </Card>

                <div className="mt-10 text-center">
                    <p className="rounded-2xl border border-black/10 bg-white/55 px-6 py-3 text-gray-900/85 dark:border-white/20 dark:bg-white/10 dark:text-white/90">
                        {t('community_support')}
                        <span className="ml-2 animate-pulse text-cyan-700 dark:text-cyan-300">
                            ✦
                        </span>
                    </p>
                </div>
            </main>
        </div>
    );
}

function TwitchIcon({ className }: { className?: string }) {
    return (
        <svg
            className={className}
            viewBox="0 0 24 24"
            fill="currentColor"
            aria-hidden="true"
        >
            <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z" />
        </svg>
    );
}
