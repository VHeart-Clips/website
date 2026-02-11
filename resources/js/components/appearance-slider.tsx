import { Appearance, useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { Monitor, Moon, Sun } from 'lucide-react';
import { ComponentType, ReactNode } from 'react';

interface AppearanceItem {
    readonly value: Appearance;
    readonly icon: ComponentType<{ className?: string }>;
    readonly label: string;
}

const APPEARANCE_ITEMS: readonly AppearanceItem[] = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
];

export default function AppearanceToggleSlider({
    className,
}: {
    className?: string;
}): ReactNode {
    const { appearance, updateAppearance } = useAppearance();

    const activeIndex = Math.max(
        0,
        APPEARANCE_ITEMS.findIndex((item) => item.value === appearance),
    );

    return (
        <div
            className={cn(
                'relative inline-flex w-23 items-center rounded-lg bg-neutral-100 p-1 sm:w-26 dark:bg-neutral-800',
                className,
            )}
            role="group"
            aria-label="Select appearance mode"
        >
            <div
                className={cn(
                    'pointer-events-none absolute top-1 left-1 h-[calc(100%-0.5rem)]',
                    'w-[calc((100%-0.5rem)/3)] rounded-md',
                    'bg-white shadow-sm ring-1 ring-neutral-200',
                    'dark:bg-neutral-700 dark:shadow-none dark:ring-neutral-600',
                    'transition-transform duration-200 ease-out',
                )}
                style={{ transform: `translateX(calc(${activeIndex} * 100%))` }}
                aria-hidden="true"
            />

            {APPEARANCE_ITEMS.map(({ value, icon: Icon, label }) => {
                const isActive = appearance === value;

                return (
                    <button
                        key={value}
                        type="button"
                        onClick={() => updateAppearance(value)}
                        className={cn(
                            'relative z-10 inline-flex h-6 flex-1 items-center justify-center rounded-md transition-colors focus-visible:ring-2 focus-visible:ring-neutral-400/60 focus-visible:outline-none dark:focus-visible:ring-neutral-500/60',
                            isActive
                                ? 'text-neutral-900 dark:text-neutral-50'
                                : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-50',
                        )}
                        aria-label={label}
                        aria-pressed={isActive}
                        title={`Switch to ${label} mode`}
                    >
                        <Icon className="size-4" aria-hidden="true" />
                        <span className="sr-only">{label}</span>
                    </button>
                );
            })}
        </div>
    );
}
