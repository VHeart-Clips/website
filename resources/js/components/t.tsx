import { Skeleton } from '@/components/ui/skeleton';
import { useTranslation } from 'react-i18next';
import React from 'react';

interface TProps {
    ns?: string;
    k: string;
    values?: Record<string, unknown>;
    loadingSkeleton?: boolean|React.ReactNode;
    children?: (text: string) => React.ReactNode;
}

/**
 *
 * @param ns The namespace to load (e.g., 'twitch', 'messages'). Defaults to 'strings'.
 * @param k The translation key.
 * @param values Dynamic values.
 * @param loadingSkeleton Show a skeleton loader while fetching the translation? Disables Suspense
 * @param children Use as a render prop to get the string (for attributes or similar).
 * @constructor
 */
export default function T({
    ns = 'strings',
    k,
    values,
    loadingSkeleton = false,
    children,
}: TProps) {
    const { t, ready } = useTranslation(ns, {
        useSuspense: !loadingSkeleton,
    });

    if (!ready) {
        if (loadingSkeleton) {
            if (typeof loadingSkeleton !== 'boolean') {
                return <>{loadingSkeleton}</>;
            }

            return (
                <Skeleton
                    className='inline-block h-4 align-middle w-full'
                />
            );
        }
        return null;
    }

    const text = t(k, values);

    if (children) {
        return <>{children(text)}</>;
    }

    return <>{text}</>;
}
