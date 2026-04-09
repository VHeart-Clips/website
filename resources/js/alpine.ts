/**
 *  AlpineJS for interactivity
 *  @see https://alpinejs.dev/start-here
 */

import anchor from '@alpinejs/anchor';
import intersect from '@alpinejs/intersect';
import AsyncAlpine from 'async-alpine';

import youtubeEmbed from '@/components/embeds/youtube-embed';
import image from '@/components/image';
import reportButton from '@/components/ui/report/button';
import baseEmbed from '@/components/embeds/base-embed';
import twitchEmbed from '@/components/embeds/twitch-embed';

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.plugin(AsyncAlpine);
    Alpine.plugin(intersect);
    Alpine.plugin(anchor);

    // Register alpine based components here, they can be used with `x-data="name({ ...config })"` in html
    Alpine.data('image', image);
    Alpine.data('baseEmbed', baseEmbed);
    Alpine.data('twitchEmbed', twitchEmbed);
    Alpine.data('youtubeEmbed', youtubeEmbed);
    Alpine.data('reportButton', reportButton);

    // These Components (and their dependencies) will be bundled on their own and only
    // get loaded if they get used (or with very low prefetch priority)
    // Make sure they use the x-load attribute to tell alpine that they are lazy/async
    // @see https://async-alpine.dev/docs/
    const asyncComponents: [string, () => Promise<unknown>][] = [
        ['modal', () => import('@/components/ui/modal')],
        ['reportModal', () => import('@/components/ui/report/modal')],
        ['appearanceSlider', () => import('@/components/appearance-slider')],
        ['clipsSlider', () => import('@/components/index/clips-slider')],
        [
            'filamentClipOverlay',
            () => import('@/components/filament/clip-overlay'),
        ],
    ];

    asyncComponents.forEach(([componentName, importFn]) => {
        Alpine.asyncData(componentName, importFn);
    });
});
