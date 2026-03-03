import { SiBluesky, SiDiscord, SiGithub, SiReddit, SiTwitch, SiX, SiYoutube } from '@icons-pack/react-simple-icons';
import { Link } from '@inertiajs/react';
import { ChevronUp } from 'lucide-react';
import React, { lazy, memo, Suspense, useLayoutEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';

import AppearanceToggleSlider from '@/components/appearance-slider';
import LanguageToggleDropdown from '@/components/language-slider';
import { cn } from '@/lib/utils';

const EasterEggContainer = lazy(
    () => import('@/components/easer-egg-container'),
);

const SOCIAL_DATA = [
    {
        id: 'gh',
        href: 'https://github.com/VHeart-Clips',
        cl: '#181717',
        cd: '#FFFFFF',
        Icon: SiGithub,
    },
    {
        id: 'dc',
        href: 'https://discord.gg/vheart',
        cl: '#5865F2',
        cd: '#5865F2',
        Icon: SiDiscord,
    },
    {
        id: 'yt',
        href: 'https://www.youtube.com/@vheartclips',
        cl: '#FF0000',
        cd: '#FF0000',
        Icon: SiYoutube,
    },
    {
        id: 'tw',
        href: 'https://www.twitch.tv/vheartclips',
        cl: '#9146FF',
        cd: '#9146FF',
        Icon: SiTwitch,
    },
    {
        id: 'x',
        href: 'https://x.com/VHeartClips',
        cl: '#000000',
        cd: '#FFFFFF',
        Icon: SiX,
    },
    {
        id: 'rd',
        href: 'https://www.reddit.com/r/VHeartClips/',
        cl: '#FF4500',
        cd: '#FF4500',
        Icon: SiReddit,
    },
    {
        id: 'bs',
        href: 'https://bsky.app/profile/vheart.net',
        cl: '#1185FE',
        cd: '#1185FE',
        Icon: SiBluesky,
    },
] as const;

const NAV_LINK = [
    { key: 'privacy.footer', path: '/privacy', static: true },
    { key: 'imprint.footer', path: '/imprint', static: true },
    { key: 'faq', path: '/faq', static: true },
    { key: 'team', path: '/team', static: true },
    { key: 'about', path: '/about-us', static: false },
];

export function Footer({ isIsland = true }: { isIsland?: boolean }) {
    const { t } = useTranslation('footer');
    const containerRef = useRef<HTMLDivElement>(null);
    const prevBodyPaddingRef = useRef<string>('');

    useLayoutEffect(() => {
        prevBodyPaddingRef.current = document.body.style.paddingBottom || '';
        const updateFooterHeight = () => {
            const height = containerRef.current
                ? Math.ceil(containerRef.current.getBoundingClientRect().height)
                : 0;
            document.documentElement.style.setProperty(
                '--footer-height',
                `${height}px`,
            );
            document.body.style.paddingBottom = `${height}px`;
        };
        updateFooterHeight();
        const resizeObserver = new ResizeObserver(updateFooterHeight);
        if (containerRef.current) resizeObserver.observe(containerRef.current);
        return () => {
            resizeObserver.disconnect();
            document.documentElement.style.removeProperty('--footer-height');
            document.body.style.paddingBottom = prevBodyPaddingRef.current;
        };
    }, []);

    return (
        <>
            <Suspense fallback={null}>
                <EasterEggContainer />
            </Suspense>

            <div
                ref={containerRef}
                className={cn(
                    'fixed right-0 bottom-0 left-0 z-40 w-full transition-all duration-300',
                    isIsland ? 'px-2 pb-2 sm:px-4 sm:pb-4' : 'px-0 pb-0',
                )}
            >
                <footer
                    className={cn(
                        'isolate w-full text-gray-900 backdrop-blur-md transition-all duration-300 dark:text-white/85',
                        'bg-gradient-to-br from-white/70 via-white/85 to-white/70 dark:bg-black/80 dark:!bg-none',
                        isIsland
                            ? 'mx-auto rounded-2xl border border-gray-200 shadow-xl ring-1 ring-black/5 dark:border-white/20'
                            : 'border-t border-gray-200 dark:border-white/20',
                    )}
                >
                    <div className="w-full overflow-hidden">
                        <details className="group xl:hidden">
                            <summary className="cursor-pointer list-none outline-none [&::-webkit-details-marker]:hidden">
                                <div
                                    className={cn(
                                        'relative flex items-center justify-center px-4',
                                        isIsland ? 'h-14' : 'h-18',
                                    )}
                                >
                                    <div className="flex items-center gap-3 text-sm font-medium transition-all duration-300">
                                        <span className="text-gray-600 dark:text-white/70">
                                            © 2026 VHeart
                                            <span className="hidden group-open:inline">
                                                . {t('all_rights_reserved')}
                                            </span>
                                        </span>

                                        <div className="flex items-center gap-3 group-open:hidden">
                                            <span className="h-3 w-px bg-gray-300 dark:bg-white/20" />
                                            <a
                                                href="/privacy"
                                                className="transition-colors hover:text-[#e9458e]"
                                            >
                                                {t('privacy.footer')}
                                            </a>
                                            <span className="h-3 w-px bg-gray-300 dark:bg-white/20" />
                                            <a
                                                href="/imprint"
                                                className="transition-colors hover:text-[#e9458e]"
                                            >
                                                {t('imprint.footer')}
                                            </a>
                                        </div>
                                    </div>

                                    <div className="absolute top-1/2 right-2 -translate-y-1/2">
                                        <div className="flex items-center justify-center rounded-xl p-2 text-white shadow-lg transition-all duration-300">
                                            <ChevronUp className="size-5 transition-transform duration-500 group-open:rotate-180" />
                                        </div>
                                    </div>
                                </div>
                            </summary>

                            <div className="flex animate-in flex-col items-center gap-6 border-t border-gray-200 px-6 py-8 duration-500 fade-in slide-in-from-bottom-4 dark:border-white/5">
                                <nav>
                                    <ul className="flex flex-wrap justify-center gap-x-6 gap-y-2">
                                        {NAV_LINK.map(
                                            ({
                                                key,
                                                path,
                                                static: isStatic,
                                            }) => {
                                                const Tag = isStatic
                                                    ? 'a'
                                                    : Link;
                                                return (
                                                    <li key={key}>
                                                        <Tag
                                                            href={path}
                                                            className="text-sm font-medium text-gray-600 hover:text-[#e9458e] dark:text-white/70 dark:hover:text-white"
                                                        >
                                                            {t(key)}
                                                        </Tag>
                                                    </li>
                                                );
                                            },
                                        )}
                                    </ul>
                                </nav>
                                <div className="flex flex-wrap justify-center gap-4">
                                    {SOCIAL_DATA.map((s) => (
                                        <a
                                            key={s.id}
                                            href={s.href}
                                            target="_blank"
                                            style={
                                                {
                                                    '--h-clr': s.cl,
                                                    '--h-clr-d': s.cd,
                                                } as React.CSSProperties
                                            }
                                            className="text-gray-500 transition-colors hover:text-(--h-clr) dark:hover:text-(--h-clr-d)"
                                        >
                                            <s.Icon className="size-6" />
                                        </a>
                                    ))}
                                </div>

                                <div className="flex items-center gap-2 rounded-xl bg-black/5 p-1.5 dark:bg-white/5">
                                    <LanguageToggleDropdown />
                                    <div className="h-4 w-px bg-gray-300 dark:bg-white/10" />
                                    <AppearanceToggleSlider />
                                </div>
                            </div>
                        </details>

                        <div
                            className={cn(
                                'hidden w-full grid-cols-[1fr_auto_1fr] items-center gap-4 px-6 xl:grid',
                                isIsland ? 'h-14' : 'h-18',
                            )}
                        >
                            <div className="flex min-w-0 items-center justify-start">
                                <div className="truncate text-sm font-medium text-gray-600 dark:text-white/70">
                                    © 2026 VHeart.{' '}
                                    <span className="ml-1 hidden opacity-50 2xl:inline">
                                        {t('all_rights_reserved')}
                                    </span>
                                </div>
                            </div>

                            <nav className="flex items-center justify-center">
                                <nav>
                                    <ul className="flex flex-wrap justify-center gap-x-6 gap-y-2">
                                        {NAV_LINK.map(
                                            ({
                                                key,
                                                path,
                                                static: isStatic,
                                            }) => {
                                                const Tag = isStatic
                                                    ? 'a'
                                                    : Link;
                                                return (
                                                    <li key={key}>
                                                        <Tag
                                                            href={path}
                                                            className="rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap text-gray-600 transition-all hover:bg-accent hover:text-accent-foreground dark:text-white/70 dark:hover:text-white"
                                                        >
                                                            {t(key)}
                                                        </Tag>
                                                    </li>
                                                );
                                            },
                                        )}
                                    </ul>
                                </nav>
                            </nav>

                            <div className="flex min-w-0 items-center justify-end gap-3">
                                <div className="flex items-center gap-0.5">
                                    {SOCIAL_DATA.map((s) => (
                                        <a
                                            key={s.id}
                                            href={s.href}
                                            target="_blank"
                                            style={
                                                {
                                                    '--h-clr': s.cl,
                                                    '--h-clr-d': s.cd,
                                                } as React.CSSProperties
                                            }
                                            className="p-2 text-gray-400 transition-colors hover:text-(--h-clr) dark:hover:text-(--h-clr-d)"
                                        >
                                            <s.Icon className="size-4" />
                                        </a>
                                    ))}
                                </div>
                                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                                <div className="flex items-center gap-1">
                                    <LanguageToggleDropdown />
                                    <AppearanceToggleSlider />
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

export default memo(Footer);
