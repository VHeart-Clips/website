import { Button } from '@/components/ui/button';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function Footer() {
    const { t } = useTranslation('footer');
    const footerRef = useRef<HTMLElement>(null);
    const [footerHeight, setFooterHeight] = useState(0);

    useEffect(() => {
        const updateFooterHeight = () => {
            if (footerRef.current) {
                const height = footerRef.current.offsetHeight;
                setFooterHeight(height);
                document.documentElement.style.setProperty(
                    '--footer-height',
                    `${height}px`,
                );
            }
        };

        updateFooterHeight();

        const resizeObserver = new ResizeObserver(updateFooterHeight);
        if (footerRef.current) {
            resizeObserver.observe(footerRef.current);
        }

        return () => {
            resizeObserver.disconnect();
        };
    }, []);

    return (
        <>
            <footer
                ref={footerRef}
                className="fixed right-0 bottom-0 left-0 z-40 border-t border-white/10 bg-white/30 py-6 text-gray-900/85 shadow-lg backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/30 dark:text-white/85"
            >
                <div className="container mx-auto px-4">
                    <div className="grid items-center gap-4 md:grid-cols-3">
                        <div className="min-w-0 text-center text-sm md:text-left">
                            © {new Date().getFullYear()} VHeart.{' '}
                            {t('all_rights_reserved')}
                        </div>

                        <nav
                            aria-label={t('footer_navigation')}
                            className="min-w-0"
                        >
                            <ul className="flex flex-wrap items-center justify-center gap-2 md:gap-3">
                                <li>
                                    <Button
                                        asChild
                                        variant="ghost"
                                        className="h-auto px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-white/70 dark:hover:text-white"
                                    >
                                        <a href="/privacy">
                                            {t('privacy.footer')}
                                        </a>
                                    </Button>
                                </li>
                                <li>
                                    <Button
                                        asChild
                                        variant="ghost"
                                        className="h-auto px-2 py-1 text-sm text-gray-600 hover:text-gray-900 dark:text-white/70 dark:hover:text-white"
                                    >
                                        <a href="/imprint">
                                            {t('imprint.footer')}
                                        </a>
                                    </Button>
                                </li>
                            </ul>
                        </nav>

                        <div className="min-w-0">
                            <div className="flex items-center justify-center gap-3 md:justify-end">
                                <a
                                    href="https://github.com/kattyterra/VHeart_Webseite"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={t('github_aria', 'GitHub')}
                                    className="text-gray-600 transition-colors hover:text-gray-900 dark:text-white/70 dark:hover:text-white"
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                                    </svg>
                                </a>

                                <a
                                    href="https://discord.gg/ThVZHqvXnD"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={t('discord_aria', 'Discord')}
                                    className="text-gray-600 transition-colors hover:text-gray-900 dark:text-white/70 dark:hover:text-white"
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515a.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0a12.64 12.64 0 00-.617-1.25a.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057a19.9 19.9 0 005.993 3.03a.078.078 0 00.084-.028a14.09 14.09 0 001.226-1.994a.076.076 0 00-.041-.106a13.107 13.107 0 01-1.872-.892a.077.077 0 01-.008-.128c.125-.094.25-.188.372-.284a.076.076 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.076.076 0 01.078.01c.12.096.245.19.37.284a.077.077 0 01-.006.127a12.3 12.3 0 01-1.873.892a.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028a19.839 19.839 0 006.002-3.03a.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z" />
                                    </svg>
                                </a>

                                <a
                                    href="https://www.youtube.com/@vheartclips"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="YouTube"
                                    className="text-gray-600 transition-colors hover:text-red-600 dark:text-white/70 dark:hover:text-red-400"
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                                    </svg>
                                </a>

                                <a
                                    href="https://www.twitch.tv/vheartclips"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Twitch"
                                    className="text-gray-600 transition-colors hover:text-purple-600 dark:text-white/70 dark:hover:text-purple-400"
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z" />
                                    </svg>
                                </a>

                                <a
                                    href="https://x.com/VHeartClips"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="X (Twitter)"
                                    className="text-gray-600 transition-colors hover:text-gray-900 dark:text-white/70 dark:hover:text-white"
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                    </svg>
                                </a>

                                <a
                                    href="https://www.reddit.com/r/VHeartClips/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Reddit"
                                    className="text-gray-600 transition-colors hover:text-orange-600 dark:text-white/70 dark:hover:text-orange-400"
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M14.238 15.348c.085.084.085.221 0 .306-.465.462-1.194.687-2.231.687l-.008-.002-.008.002c-1.036 0-1.766-.225-2.231-.688-.085-.084-.085-.221 0-.305.084-.084.222-.084.307 0 .379.377 1.008.561 1.924.561l.008.002.008-.002c.915 0 1.544-.184 1.924-.561.085-.084.223-.084.307 0zm-3.44-2.418c0-.507-.414-.919-.922-.919-.509 0-.923.412-.923.919 0 .506.414.918.923.918.508.001.922-.411.922-.918zm13.202-.93c0 6.627-5.373 12-12 12s-12-5.373-12-12 5.373-12 12-12 12 5.373 12 12zm-5-.129c0-.851-.695-1.543-1.55-1.543-.417 0-.795.167-1.074.435-1.056-.695-2.485-1.137-4.066-1.194l.865-2.724 2.343.549-.003.034c0 .696.569 1.262 1.268 1.262.699 0 1.267-.566 1.267-1.262s-.568-1.262-1.267-1.262c-.537 0-.994.335-1.179.804l-2.525-.592c-.11-.027-.223.037-.257.145l-.965 3.038c-1.656.02-3.155.466-4.258 1.181-.277-.255-.644-.415-1.05-.415-.854.001-1.549.693-1.549 1.544 0 .566.311 1.056.768 1.325-.03.164-.05.331-.05.5 0 2.281 2.805 4.137 6.253 4.137s6.253-1.856 6.253-4.137c0-.16-.017-.317-.044-.472.486-.261.82-.766.82-1.353zm-4.872.141c-.509 0-.922.412-.922.919 0 .506.414.918.922.918s.922-.412.922-.918c0-.507-.413-.919-.922-.919z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>

            <div
                style={{ height: `${footerHeight}px` }}
                className="pointer-events-none opacity-0"
                aria-hidden="true"
            />
        </>
    );
}
