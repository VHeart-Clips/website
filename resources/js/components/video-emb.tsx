interface VideoCardProps {
    youtubeUrl?: string;
}

export default function VideoEmb({youtubeUrl }: VideoCardProps) {
    return (
        <div className="relative aspect-video overflow-hidden rounded-xl border border-gray-300/80 bg-white/85 dark:border-white/15 dark:bg-black/40">
            <iframe
                width="100%"
                height="100%"
                src={youtubeUrl}
                title="YouTube video player"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerPolicy="strict-origin-when-cross-origin"
                allowFullScreen
            ></iframe>
        </div>
    );
}
