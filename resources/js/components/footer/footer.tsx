import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';

export default function Footer() {
    const { t } = useTranslation('footer');

    return (
        <footer className="border-t border-white/10 py-6">
            <div className="container mx-auto px-4">
                <div className="grid items-center gap-4 md:grid-cols-3">
                    <div className="min-w-0 text-center text-sm text-white/60 md:text-left">
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
                                    className="h-auto px-2 py-1 text-sm text-white/70 hover:text-white"
                                >
                                    <a href="https://www.youtube.com/watch?v=qHnLR5W7BfE">
                                        {t('privacy.footer')}
                                    </a>
                                </Button>
                            </li>
                            <li>
                                <Button
                                    asChild
                                    variant="ghost"
                                    className="h-auto px-2 py-1 text-sm text-white/70 hover:text-white"
                                >
                                    <a href="/imprint">
                                        {t('imprint.footer')}
                                    </a>
                                </Button>
                            </li>
                            <li>
                                <Button
                                    asChild
                                    variant="ghost"
                                    className="h-auto px-2 py-1 text-sm text-white/70 hover:text-white"
                                >
                                    <a href="/terms">
                                        {t('terms.footer')}
                                    </a>
                                </Button>
                            </li>
                        </ul>
                    </nav>

                    <div className="min-w-0">
                        <div className="flex items-center justify-center gap-4 md:justify-end">
                            <a
                                href="https://github.com/kattyterra/VHeart_Webseite"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label={t('github_aria', 'GitHub')}
                                className="text-white/70 hover:text-white"
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
                                className="text-white/70 hover:text-white"
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
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
