import { Card } from '@/components/ui/card';
import { Loader2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

interface BetterplaceDonationCardProps {
    projectId?: number;
    eventId?: number;
    maxVisibleDonations?: number;
    refreshInterval?: number;
}

interface Donation {
    id: number;
    donated_amount_in_cents: number;
    author?: {
        name?: string;
        picture?: {
            links: Array<{ href: string }>;
        };
    };
    donator_name?: string;
    donator_picture?: string;
    amount_in_cents?: number;
}

interface ProjectData {
    id?: number;
    title: string;
    description: string;
    donated_amount_in_cents: number;
    goal_amount_in_cents?: number;
    open_amount_in_cents?: number;
    donations_count: number;
}

export function BetterplaceDonationCard({
    projectId = 58353,
    eventId,
    maxVisibleDonations = 8,
    refreshInterval = 300000,
}: BetterplaceDonationCardProps) {
    const [project, setProject] = useState<ProjectData>({
        title: '',
        description: '',
        donated_amount_in_cents: 0,
        goal_amount_in_cents: 0,
        donations_count: 0,
    });

    const [donations, setDonations] = useState<Donation[]>([]);
    const [loadingDonations, setLoadingDonations] = useState(false);
    const [loadingProject, setLoadingProject] = useState(false);
    const [, setDonationProgress] = useState(0);
    const [donatedAmountFormatted, setDonatedAmountFormatted] =
        useState('0,00');
    const [, setGoalAmountFormatted] = useState('0,00');
    const [error, setError] = useState<string>('');

    const formatCurrency = useCallback((amount: number) => {
        return amount.toLocaleString('de-DE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }, []);

    const fetchDonationData = useCallback(async () => {
        try {
            setLoadingProject(true);
            setError('');

            const apiUrl = eventId
                ? `https://api.betterplace.org/de/api_v4/fundraising_events/${eventId}.json`
                : `https://api.betterplace.org/de/api_v4/projects/${projectId}.json`;

            const response = await fetch(apiUrl);
            const projectData = await response.json();

            if (projectData) {
                const totalGoal =
                    projectData.donated_amount_in_cents +
                    (projectData.open_amount_in_cents ||
                        projectData.goal_amount_in_cents ||
                        0);

                const newProgress =
                    totalGoal > 0
                        ? (
                              (projectData.donated_amount_in_cents /
                                  totalGoal) *
                              100
                          ).toFixed(2)
                        : '0.00';

                setDonationProgress(parseFloat(newProgress));

                setProject({
                    id: projectData.id,
                    title: projectData.title,
                    description: projectData.description || '',
                    donated_amount_in_cents:
                        projectData.donated_amount_in_cents,
                    goal_amount_in_cents: totalGoal,
                    donations_count: projectData.donations_count,
                });

                setDonatedAmountFormatted(
                    formatCurrency(projectData.donated_amount_in_cents / 100),
                );
                setGoalAmountFormatted(formatCurrency(totalGoal / 100));
            }
        } catch (err) {
            console.error('Error fetching donation data:', err);
            setError('Spenden-Daten konnten nicht geladen werden.');
        } finally {
            setLoadingProject(false);
        }
    }, [eventId, projectId, formatCurrency]);

    const fetchDonations = useCallback(async () => {
        setLoadingDonations(true);
        try {
            setError('');

            const donationsUrl = eventId
                ? `https://api.betterplace.org/de/api_v4/fundraising_events/${eventId}/opinions.json`
                : `https://api.betterplace.org/de/api_v4/projects/${projectId}/opinions.json`;

            const response = await fetch(donationsUrl);
            const donationsData = await response.json();

            setDonations(donationsData?.data || []);
        } catch (err) {
            console.error('Error fetching donations:', err);
            setError('Spenden konnten nicht geladen werden.');
            setDonations([]);
        } finally {
            setLoadingDonations(false);
        }
    }, [eventId, projectId]);

    useEffect(() => {
        fetchDonationData();
        fetchDonations();

        const interval = setInterval(() => {
            fetchDonationData();
            fetchDonations();
        }, refreshInterval);

        return () => clearInterval(interval);
    }, [fetchDonationData, fetchDonations, refreshInterval]);

    const visibleDonations = donations.slice(0, maxVisibleDonations);

    return (
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-6 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
                {loadingProject ? (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-gray-400" />
                        <span className="ml-3 text-gray-600 dark:text-gray-300">
                            Lade Projekt...
                        </span>
                    </div>
                ) : (
                    <>
                        <div className="mb-6">
                            <h2 className="mb-3 bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-2xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                {project.title || 'Spendenprojekt'}
                            </h2>
                            {project.description && (
                                <div
                                    className="prose prose-sm dark:prose-invert max-w-none text-sm leading-relaxed text-gray-700 dark:text-gray-300"
                                    dangerouslySetInnerHTML={{
                                        __html: project.description,
                                    }}
                                />
                            )}
                        </div>

                        <div className="relative py-6">
                            <div className="absolute inset-0 flex items-center justify-center">
                                <div className="h-32 w-32 rounded-full bg-gradient-to-r from-emerald-500/10 via-teal-400/10 to-cyan-400/10 blur-2xl" />
                            </div>

                            <div className="relative text-center">
                                <div className="mb-3 flex items-center justify-center gap-3">
                                    <div className="h-2 w-2 animate-pulse rounded-full bg-emerald-500" />
                                    <span className="text-sm font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                                        Gesamtsumme
                                    </span>
                                    <div className="h-2 w-2 animate-pulse rounded-full bg-cyan-500" />
                                </div>

                                <div className="relative inline-block">
                                    <div className="animate-gradient bg-gradient-to-r from-emerald-600 via-teal-500 to-cyan-500 bg-clip-text text-4xl font-bold text-transparent lg:text-5xl">
                                        {donatedAmountFormatted} €
                                    </div>
                                    <div className="absolute -top-2 -right-2"></div>
                                </div>
                            </div>
                        </div>
                    </>
                )}
            </Card>

            <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-6 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
                <h3 className="mb-4 text-xl font-bold text-gray-900 dark:text-white">
                    Letzte Spenden
                </h3>

                {error && (
                    <div className="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-200">
                        {error}
                    </div>
                )}

                {loadingDonations ? (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-gray-400" />
                        <span className="ml-3 text-gray-600 dark:text-gray-300">
                            Lade Spenden...
                        </span>
                    </div>
                ) : donations.length > 0 ? (
                    <div className="space-y-2">
                        <div className="grid grid-cols-2 gap-4 border-b border-gray-200 pb-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">
                            <div>Spender</div>
                            <div className="text-right">Betrag</div>
                        </div>

                        <div className="max-h-80 overflow-y-auto">
                            {visibleDonations.map((donation, index) => {
                                const donorName =
                                    donation.author?.name ||
                                    donation.donator_name ||
                                    'Anonym';
                                const donorPicture =
                                    donation.author?.picture?.links?.[0]
                                        ?.href || donation.donator_picture;
                                const amountInCents =
                                    donation.donated_amount_in_cents ||
                                    donation.amount_in_cents ||
                                    0;

                                return (
                                    <div
                                        key={donation.id || index}
                                        className="grid grid-cols-2 gap-4 border-b border-gray-100 py-3 last:border-0 dark:border-gray-800"
                                    >
                                        <div className="flex items-center gap-3">
                                            {donorPicture && (
                                                <img
                                                    src={donorPicture}
                                                    alt={donorName}
                                                    className="h-8 w-8 rounded-full border-2 border-gray-200 dark:border-gray-700"
                                                />
                                            )}
                                            <span className="text-sm text-gray-800 dark:text-gray-200">
                                                {donorName}
                                            </span>
                                        </div>
                                        <div className="text-right font-bold text-emerald-600 dark:text-emerald-400">
                                            {formatCurrency(
                                                amountInCents / 100,
                                            )}{' '}
                                            €
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                ) : (
                    <div className="py-12 text-center text-gray-500 dark:text-gray-400">
                        Noch keine Spenden vorhanden
                    </div>
                )}
            </Card>
        </div>
    );
}
