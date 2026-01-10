import { Card } from '@/components/ui/card';
import { CheckCircle, Shield, Users, Video, Vote } from 'lucide-react';

interface ClipProcessCardProps {
    t: (key: string) => string;
}

export default function ClipProcessCard({ t }: ClipProcessCardProps) {
    const clipProcess = [
        {
            icon: Vote,
            title: t('clip_process.steps.community.title'),
            description: t('clip_process.steps.community.description'),
        },
        {
            icon: Users,
            title: t('clip_process.steps.jury.title'),
            description: t('clip_process.steps.jury.description'),
        },
        {
            icon: CheckCircle,
            title: t('clip_process.steps.consent.title'),
            description: t('clip_process.steps.consent.description'),
        },
        {
            icon: Video,
            title: t('clip_process.steps.edit.title'),
            description: t('clip_process.steps.edit.description'),
        },
    ];

    return (
        <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-8 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
            <div className="mb-8">
                <div className="mb-10 text-center">
                    <div className="mb-4 flex items-center justify-center gap-3">
                        <Video className="h-6 w-6 text-gray-900/90 dark:text-white" />
                        <h2 className="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-3xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                            {t('clip_process.title')}
                        </h2>
                    </div>
                    <p className="mx-auto max-w-3xl text-base leading-relaxed text-gray-800 dark:text-white/90">
                        {t('clip_process.intro')}
                    </p>
                </div>

                <div className="mb-8 grid gap-5 md:grid-cols-2">
                    {clipProcess.map((step, idx) => (
                        <div
                            key={idx}
                            className="rounded-xl border border-gray-300/80 bg-white/65 p-6 transition-transform duration-200 hover:scale-[1.02] dark:border-white/10 dark:bg-black/25"
                        >
                            <div className="mb-4 flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                    <step.icon className="h-5 w-5 text-gray-900/90 dark:text-white" />
                                </div>
                                <h3 className="text-lg font-bold text-gray-900/90 dark:text-white/90">
                                    {step.title}
                                </h3>
                            </div>
                            <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                {step.description}
                            </p>
                        </div>
                    ))}
                </div>

                <div className="mb-8 rounded-xl border border-gray-300/80 bg-white/65 p-6 dark:border-white/10 dark:bg-black/25">
                    <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                        {t('clip_process.neutrality')}
                    </p>
                </div>

                <div className="rounded-xl border border-red-300 bg-red-50/80 p-6 dark:border-red-400/30 dark:bg-red-900/10">
                    <div className="flex items-start gap-3">
                        <Shield className="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-300" />
                        <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                            {t('clip_process.blacklist')}
                        </p>
                    </div>
                </div>
            </div>
        </Card>
    );
}
