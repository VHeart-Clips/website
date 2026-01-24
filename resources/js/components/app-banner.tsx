import { cn } from '@/lib/utils';
import { type ReactNode, useEffect, useLayoutEffect, useRef, useState } from 'react';

interface AppBannerProps {
    visible: boolean;
    children: ReactNode;
    className?: string;
    offsetVariable?: string;
}

export default function AppBanner({
    visible,
    children,
    className,
    offsetVariable,
}: AppBannerProps) {
    const contentRef = useRef<HTMLDivElement | null>(null);
    const [height, setHeight] = useState(0);

    useLayoutEffect(() => {
        const target = contentRef.current;
        if (!target) {
            return;
        }

        const updateHeight = () => {
            setHeight(Math.ceil(target.getBoundingClientRect().height));
        };

        updateHeight();

        const observer = new ResizeObserver(updateHeight);
        observer.observe(target);

        return () => {
            observer.disconnect();
        };
    }, []);

    useEffect(() => {
        if (!offsetVariable) {
            return;
        }

        const value = visible ? `${height}px` : '0px';
        document.documentElement.style.setProperty(offsetVariable, value);

        return () => {
            document.documentElement.style.setProperty(offsetVariable, '0px');
        };
    }, [offsetVariable, visible, height]);

    return (
        <div
            className={cn(
                'overflow-hidden transition-[height] duration-300 ease-out',
                className,
            )}
            style={{ height: visible ? height : 0 }}
            aria-hidden={!visible}
        >
            <div
                ref={contentRef}
                className={cn(
                    'transition-opacity duration-200 ease-out',
                    visible ? 'opacity-100' : 'pointer-events-none opacity-0',
                )}
            >
                {children}
            </div>
        </div>
    );
}
