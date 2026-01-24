import clsx from 'clsx'


type TwitchClipProps = {
    slug: string
    parent: string
    className?: string
}

export function TwitchClipContainer({
    slug,
    parent,
    className,
}: TwitchClipProps) {

    return (
        <iframe
            src={`https://clips.twitch.tv/embed?clip=${slug}&parent=${parent}&autoplay=false&muted=false`}
            allow="fullscreen"
            allowFullScreen
            className={clsx(className)}
        />

    )
}
