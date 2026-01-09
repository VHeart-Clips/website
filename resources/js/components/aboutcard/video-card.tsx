import { Card, CardContent } from '@/components/ui/card';
import VideoEmb from '@/components/video-emb';


interface VideoCardProps {
    t: (key: string) => string;
    youtubeUrl?: string;
}

export default function VideoCard({ t, youtubeUrl }: VideoCardProps) {
    return (
        <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 p-8 shadow-2xl ring-1 shadow-black/10 ring-black/5 dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
            <CardContent className="p-6">
                <div className="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <span className="text-xs tracking-widest text-gray-700 uppercase dark:text-white/70">
                            {t('video.latest_label')}
                        </span>
                    </div>
                </div>
                <VideoEmb youtubeUrl={youtubeUrl} />
            </CardContent>
        </Card>
    );
}
