import { Clock, Heart } from 'lucide-react';

type ClipPreviewProps = {
    thumbUrl: string;
    title: string;
    likes: number;
    lengthSeconds: number;
    broadcasterName?: string;
    onClick?: () => void;
};

const formatDuration = (seconds: number) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
};

export function ClipPreview({
    thumbUrl,
    title,
    likes,
    lengthSeconds,
    broadcasterName,
    onClick,
}: ClipPreviewProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            aria-label={`Clip öffnen: ${title}`}
            className="group relative aspect-video w-full overflow-hidden rounded-md bg-gray-400 drop-shadow-md dark:drop-shadow-white/20"
        >
            <img
                src={thumbUrl}
                alt={title}
                className="h-full w-full object-cover"
                loading="lazy"
            />

            {/* Länge */}
            <div className="absolute top-2 left-2 flex items-center gap-1 rounded-lg bg-black/60 px-2 py-1 text-xs text-white">
                <Clock className="h-4 w-4" />
                {formatDuration(lengthSeconds)}
            </div>

            {/* Likes */}
            <div className="absolute top-2 right-2 flex items-center gap-1 rounded-lg bg-black/60 px-2 py-1 text-xs text-white">
                <Heart className="h-4 w-4 text-red-500" />
                {likes}
            </div>

            {/* Titel unten */}
            <div className="absolute right-2 bottom-0.5 left-2 rounded-xl bg-black/75 px-2 py-1 text-white">
                <div className="line-clamp-1 text-sm font-medium">{title}</div>

                {broadcasterName && (
                    <div className="truncate text-xs text-white/80">
                        {broadcasterName}
                    </div>
                )}
            </div>
        </button>
    );
}
