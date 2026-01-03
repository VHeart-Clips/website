import Footer from '@/components/footer/footer';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import PartnerIcon from '/resources/images/svg/logo-dark.svg';

import {
    CheckCircle,
    Heart,
    LogIn,
    Shield,
    Sparkles,
    Users,
    Video,
    Vote,
} from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

type Star = {
    x: number;
    y: number;
    size: number;
    speed: number;
    brightness: number;
    pulseSpeed: number;
    twinkle: number;
};

type Nebula = {
    x: number;
    y: number;
    radius: number;
    color: string;
    speedX: number;
    speedY: number;
};

type ShootingStar = {
    active: boolean;
    x: number;
    y: number;
    vx: number;
    vy: number;
    life: number;
    trail: { x: number; y: number; life: number }[];
};

const CANVAS_THEMES = {
    dark: {
        background: '#0a0a1a',
        planetGradientStops: [
            { offset: 0, color: 'rgba(100, 65, 165, 0.8)' },
            { offset: 0.6, color: 'rgba(70, 35, 135, 0.6)' },
            { offset: 1, color: 'rgba(40, 20, 80, 0.4)' },
        ],
        ringGradientStops: [
            { offset: 0, color: 'rgba(145, 70, 255, 0)' },
            { offset: 0.3, color: 'rgba(145, 70, 255, 0.3)' },
            { offset: 0.7, color: 'rgba(145, 70, 255, 0.3)' },
            { offset: 1, color: 'rgba(145, 70, 255, 0)' },
        ],
        nebulaColors: [
            'rgba(145, 70, 255, 0.1)',
            'rgba(0, 174, 255, 0.07)',
            'rgba(255, 70, 145, 0.05)',
        ],
        starColor: 255,
        starAlphaMultiplier: 1,
        shootingStarColor: 'rgba(255,255,255,0.9)',
        starSpeedMin: 0.1,
        starSpeedMax: 0.4,
        shootingStarSpeed: 15,
    },
    light: {
        background: '#EEF2F8',
        planetGradientStops: [
            { offset: 0, color: 'rgba(155, 120, 220, 0.75)' },
            { offset: 0.6, color: 'rgba(130, 95, 200, 0.55)' },
            { offset: 1, color: 'rgba(110, 80, 180, 0.35)' },
        ],
        ringGradientStops: [
            { offset: 0, color: 'rgba(145, 70, 255, 0)' },
            { offset: 0.3, color: 'rgba(145, 70, 255, 0.18)' },
            { offset: 0.7, color: 'rgba(145, 70, 255, 0.18)' },
            { offset: 1, color: 'rgba(145, 70, 255, 0)' },
        ],
        nebulaColors: [
            'rgba(145, 70, 255, 0.06)',
            'rgba(0, 174, 255, 0.05)',
            'rgba(255, 70, 145, 0.04)',
        ],
        starColor: 80,
        starAlphaMultiplier: 0.35,
        shootingStarColor: 'rgba(90,90,90,0.9)',
        starSpeedMin: 0.05,
        starSpeedMax: 0.23,
        shootingStarSpeed: 10,
    },
};

export default function Welcome() {
    const { t } = useTranslation('welcome');

    const [isMobile, setIsMobile] = useState(false);

    const [isDarkMode, setIsDarkMode] = useState(() => {
        if (typeof document === 'undefined') return false;
        return document.documentElement.classList.contains('dark');
    });

    const canvasRef = useRef<HTMLCanvasElement>(null);
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-expect-error
    const animationRef = useRef<number>();

    const starsRef = useRef<Star[]>([]);
    const nebulasRef = useRef<Nebula[]>([]);
    const shootingPoolRef = useRef<ShootingStar[]>([]);
    const timeRef = useRef(0);
    const lastSpawnRef = useRef(0);

    const dprRef = useRef(1);
    const sizeRef = useRef({ w: 0, h: 0 });
    const isVisibleRef = useRef(true);
    const lastFrameTimeRef = useRef(0);

    const youtubeUrl = 'https://youtu.be/7Z_M_YhxjOM';

    useEffect(() => {
        const checkMobile = () => setIsMobile(window.innerWidth < 768);
        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    useEffect(() => {
        const checkTheme = () => {
            setIsDarkMode(document.documentElement.classList.contains('dark'));
        };

        checkTheme();

        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.attributeName === 'class') checkTheme();
            }
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        return () => observer.disconnect();
    }, []);

    const initStars = useCallback(
        (w: number, h: number) => {
            const canvasTheme = isDarkMode
                ? CANVAS_THEMES.dark
                : CANVAS_THEMES.light;

            const stars: Star[] = [];
            const starCount = isMobile
                ? Math.min(150, w / 10)
                : Math.min(200, w / 8);

            for (let i = 0; i < starCount; i++) {
                stars.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    size: Math.random() * 2 + 0.5,
                    speed:
                        Math.random() *
                            (canvasTheme.starSpeedMax -
                                canvasTheme.starSpeedMin) +
                        canvasTheme.starSpeedMin,
                    brightness: Math.random() * 0.6 + 0.4,
                    pulseSpeed: Math.random() * 0.01 + 0.005,
                    twinkle: Math.random() * Math.PI * 2,
                });
            }
            return stars;
        },
        [isDarkMode, isMobile],
    );

    const initNebulas = useCallback(
        (w: number, h: number) => {
            const canvasTheme = isDarkMode
                ? CANVAS_THEMES.dark
                : CANVAS_THEMES.light;

            return Array.from({ length: 4 }).map(() => ({
                x: Math.random() * w,
                y: Math.random() * h,
                radius: Math.random() * 150 + 100,
                speedX: Math.random() * 0.08 - 0.04,
                speedY: Math.random() * 0.08 - 0.04,
                color: canvasTheme.nebulaColors[
                    Math.floor(Math.random() * canvasTheme.nebulaColors.length)
                ],
            }));
        },
        [isDarkMode],
    );

    const ensureShootingPool = useCallback(() => {
        if (shootingPoolRef.current.length) return;
        shootingPoolRef.current = Array.from({ length: 4 }).map(() => ({
            active: false,
            x: 0,
            y: 0,
            vx: 0,
            vy: 0,
            life: 0,
            trail: [],
        }));
    }, []);

    const spawnShootingStar = useCallback(
        (w: number) => {
            const canvasTheme = isDarkMode
                ? CANVAS_THEMES.dark
                : CANVAS_THEMES.light;

            const pool = shootingPoolRef.current;
            const s = pool.find((p) => !p.active);
            if (!s) return;

            s.active = true;
            s.x = Math.random() * w;
            s.y = 0;
            const speed = canvasTheme.shootingStarSpeed;
            s.vx = -speed * 0.7;
            s.vy = speed;
            s.life = 1;
            s.trail = [];
        },
        [isDarkMode],
    );

    const setCanvasSize = useCallback(
        (canvas: HTMLCanvasElement, ctx: CanvasRenderingContext2D) => {
            const dpr = Math.max(1, window.devicePixelRatio || 1);
            dprRef.current = dpr;

            const cssW = window.innerWidth;
            const cssH = window.innerHeight;
            sizeRef.current = { w: cssW, h: cssH };

            canvas.width = Math.floor(cssW * dpr);
            canvas.height = Math.floor(cssH * dpr);
            canvas.style.width = `${cssW}px`;
            canvas.style.height = `${cssH}px`;

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            starsRef.current = initStars(cssW, cssH);
            nebulasRef.current = initNebulas(cssW, cssH);
        },
        [initStars, initNebulas],
    );

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        ensureShootingPool();

        let resizeRaf = 0;
        const onResize = () => {
            if (resizeRaf) return;
            resizeRaf = requestAnimationFrame(() => {
                resizeRaf = 0;
                setCanvasSize(canvas, ctx);
            });
        };

        const handleVisibilityChange = () => {
            isVisibleRef.current = document.visibilityState === 'visible';
        };

        setCanvasSize(canvas, ctx);
        window.addEventListener('resize', onResize, { passive: true });
        document.addEventListener('visibilitychange', handleVisibilityChange);

        const drawPlanet = (
            x: number,
            y: number,
            radius: number,
            time: number,
        ) => {
            const canvasTheme = isDarkMode
                ? CANVAS_THEMES.dark
                : CANVAS_THEMES.light;

            ctx.save();
            ctx.translate(x, y);

            const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, radius);
            canvasTheme.planetGradientStops.forEach(({ offset, color }) => {
                gradient.addColorStop(offset, color);
            });

            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(0, 0, radius, 0, Math.PI * 2);
            ctx.fill();

            ctx.rotate(time * 0.1);

            const ringGradient = ctx.createLinearGradient(
                -radius * 1.5,
                0,
                radius * 1.5,
                0,
            );
            canvasTheme.ringGradientStops.forEach(({ offset, color }) => {
                ringGradient.addColorStop(offset, color);
            });

            ctx.strokeStyle = ringGradient;
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.ellipse(0, 0, radius * 1.5, radius * 0.3, 0, 0, Math.PI * 2);
            ctx.stroke();

            ctx.restore();
        };

        lastFrameTimeRef.current = performance.now();

        const animate = (ts: number) => {
            if (!isVisibleRef.current) {
                animationRef.current = requestAnimationFrame(animate);
                return;
            }

            const { w, h } = sizeRef.current;
            const canvasTheme = isDarkMode
                ? CANVAS_THEMES.dark
                : CANVAS_THEMES.light;

            const dt = Math.min(32, ts - lastFrameTimeRef.current);
            lastFrameTimeRef.current = ts;
            timeRef.current += dt * 0.001;

            ctx.fillStyle = canvasTheme.background;
            ctx.fillRect(0, 0, w, h);

            const nebulas = nebulasRef.current;
            for (let i = 0; i < nebulas.length; i++) {
                const n = nebulas[i];
                n.x += n.speedX * (dt / 16.67);
                n.y += n.speedY * (dt / 16.67);

                if (n.x > w + 200) n.x = -200;
                if (n.x < -200) n.x = w + 200;
                if (n.y > h + 200) n.y = -200;
                if (n.y < -200) n.y = h + 200;

                const gradient = ctx.createRadialGradient(
                    n.x,
                    n.y,
                    0,
                    n.x,
                    n.y,
                    n.radius,
                );
                gradient.addColorStop(0, n.color);
                gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(n.x, n.y, n.radius, 0, Math.PI * 2);
                ctx.fill();
            }

            if (w > 768) {
                drawPlanet(w * 0.8, h * 0.2, 60, timeRef.current);
                drawPlanet(w * 0.2, h * 0.7, 40, timeRef.current * 1.2);
            }

            const stars = starsRef.current;
            for (let i = 0; i < stars.length; i++) {
                const s = stars[i];
                s.y += s.speed * (dt / 16.67);
                if (s.y > h) {
                    s.y = 0;
                    s.x = Math.random() * w;
                }
                s.twinkle += s.pulseSpeed * (dt / 16.67);
                s.brightness = 0.5 + Math.sin(s.twinkle) * 0.3;

                ctx.save();

                const gradient = ctx.createRadialGradient(
                    s.x,
                    s.y,
                    0,
                    s.x,
                    s.y,
                    s.size * 3,
                );

                const starAlpha =
                    s.brightness * canvasTheme.starAlphaMultiplier;
                const starColor = canvasTheme.starColor;

                gradient.addColorStop(
                    0,
                    `rgba(${starColor}, ${starColor}, ${starColor}, ${starAlpha})`,
                );
                gradient.addColorStop(
                    0.5,
                    `rgba(${starColor}, ${starColor}, ${starColor}, ${starAlpha * 0.3})`,
                );
                gradient.addColorStop(
                    1,
                    `rgba(${starColor}, ${starColor}, ${starColor}, 0)`,
                );

                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
                ctx.fill();

                ctx.restore();
            }

            if (timeRef.current - lastSpawnRef.current > 2) {
                lastSpawnRef.current = timeRef.current;
                if (Math.random() < 0.02) spawnShootingStar(w);
            }

            const pool = shootingPoolRef.current;
            for (let i = 0; i < pool.length; i++) {
                const s = pool[i];
                if (!s.active) continue;

                s.life -= 0.05 * (dt / 16.67);
                s.x += s.vx * (dt / 16.67);
                s.y += s.vy * (dt / 16.67);

                if (s.life <= 0 || s.y > h + 50) {
                    s.active = false;
                    continue;
                }

                ctx.fillStyle = canvasTheme.shootingStarColor;
                ctx.beginPath();
                ctx.arc(s.x, s.y, 3, 0, Math.PI * 2);
                ctx.fill();
            }

            animationRef.current = requestAnimationFrame(animate);
        };

        animationRef.current = requestAnimationFrame(animate);

        return () => {
            window.removeEventListener('resize', onResize);
            document.removeEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
            if (resizeRaf) cancelAnimationFrame(resizeRaf);
            if (animationRef.current)
                cancelAnimationFrame(animationRef.current);
        };
    }, [ensureShootingPool, setCanvasSize, spawnShootingStar, isDarkMode]);

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
        <>
            <Head title={t('meta.title')} />

            <div className="relative flex min-h-screen flex-col overflow-hidden bg-blue-50 dark:bg-[#0a0a1a]">
                <canvas
                    ref={canvasRef}
                    className="pointer-events-none fixed inset-0"
                    style={{ zIndex: 0 }}
                />
                <div
                    className="fixed inset-0 bg-gradient-to-t from-blue-200/55 via-blue-100/30 to-blue-200/45 dark:from-[#0a0a1a]/90 dark:via-transparent dark:to-[#0a0a1a]/80"
                    style={{ zIndex: 1 }}
                />
                <div
                    className="fixed inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.12)_0%,transparent_55%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.10)_0%,transparent_55%)] dark:bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.15)_0%,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.10)_0%,transparent_50%)]"
                    style={{ zIndex: 1 }}
                />
                <main className="relative z-10 flex flex-1 items-center justify-center px-4 py-12">
                    <div className="w-full max-w-[1200px] space-y-8">
                        <div className="flex justify-end px-2">
                            <Link
                                href="/login"
                                className="group relative inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 hover:scale-105"
                            >
                                <span className="absolute -inset-1 rounded-full bg-gradient-to-r from-purple-600/25 to-cyan-500/20 opacity-60 blur-lg transition-opacity group-hover:opacity-90 dark:from-purple-600/35 dark:to-cyan-500/30" />
                                <span className="relative inline-flex items-center gap-2 rounded-full border border-purple-300/60 bg-gradient-to-r from-purple-100/90 to-cyan-100/80 px-5 py-2.5 text-purple-800 shadow-lg shadow-black/5 backdrop-blur-sm transition-all duration-300 group-hover:border-purple-400/70 group-hover:shadow-xl dark:border-purple-400/30 dark:bg-gradient-to-r dark:from-purple-500/20 dark:via-transparent dark:to-cyan-500/20 dark:text-white/90 dark:shadow-black/30 dark:group-hover:border-purple-400/50">
                                    <span className="relative">
                                        <LogIn className="h-4 w-4" />
                                        <Sparkles className="absolute -top-1 -right-1 h-2 w-2 text-cyan-500 opacity-0 transition-opacity group-hover:opacity-100 dark:text-cyan-300" />
                                    </span>
                                    {t('login.members')}
                                </span>
                            </Link>
                        </div>

                        <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 shadow-2xl ring-1 shadow-black/10 ring-black/5 backdrop-blur-xl dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
                            <div className="px-6 py-8 sm:px-10 sm:py-12">
                                <div className="mx-auto max-w-5xl">
                                    <div className="mb-10 text-center">
                                        <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                                            {t('hero.title_prefix')}{' '}
                                            <span className="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                                {t('hero.brand')}
                                            </span>
                                        </h1>

                                        <p className="mx-auto mt-6 max-w-3xl text-base leading-relaxed text-gray-800 sm:text-lg dark:text-white/90">
                                            {t('hero.description')}
                                        </p>

                                        <div className="mt-6 flex flex-wrap justify-center gap-2">
                                            {[
                                                t('hero.tags.tag1'),
                                                t('hero.tags.tag2'),
                                                t('hero.tags.tag3'),
                                            ].map((tag, idx) => (
                                                <span
                                                    key={idx}
                                                    className="rounded-full border border-gray-300/80 bg-gradient-to-r from-purple-100/80 to-cyan-100/70 px-3 py-1.5 text-sm font-medium text-gray-900/90 dark:border-white/15 dark:bg-gradient-to-r dark:from-purple-500/20 dark:to-cyan-500/20 dark:text-white/85"
                                                >
                                                    {tag}
                                                </span>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="my-8 border-t border-gray-300/80 dark:border-white/10" />

                                    <div className="mb-10 grid gap-8 lg:grid-cols-2">
                                        <div className="space-y-6">
                                            <div className="flex items-center gap-3">
                                                <div className="rounded-xl border border-gray-300/80 bg-white/60 p-2.5 dark:border-white/20 dark:bg-black/20">
                                                    <Users className="h-6 w-6 text-gray-900/90 dark:text-white" />
                                                </div>
                                                <h2 className="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-2xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                                    {t('about.title')}
                                                </h2>
                                            </div>

                                            <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                                {t('about.p1')}
                                            </p>

                                            <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                                {t('about.p2')}
                                            </p>

                                            <div className="mt-4 rounded-xl border border-gray-300/80 bg-white/60 p-4 dark:border-white/15 dark:bg-black/20">
                                                <p className="text-sm font-bold text-gray-900/90 dark:text-white/90">
                                                    {t('about.hashtag')}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="rounded-xl border border-gray-300/80 bg-white/60 p-6 dark:border-white/15 dark:bg-black/20">
                                            <div className="mb-4 flex items-start gap-4">
                                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                                    <img
                                                        src={PartnerIcon}
                                                        alt="logo"
                                                        className="h-12 w-12 object-contain"
                                                    />
                                                </div>

                                                <div>
                                                    <h3 className="mb-2 bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-2xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                                        {t('donation.title')}
                                                    </h3>
                                                    <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                                        {t('donation.intro')}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="mb-6 space-y-4">
                                                <div className="rounded-lg bg-purple-50/80 p-4 dark:bg-purple-900/20">
                                                    <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                                        <span className="font-bold">
                                                            {t(
                                                                'donation.hashtag',
                                                            )}
                                                        </span>
                                                    </p>
                                                </div>

                                                <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                                    {t('donation.partner_p1', {
                                                        partner: t(
                                                            'donation.partner_placeholder',
                                                        ),
                                                    })}
                                                </p>

                                                <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                                    {t('donation.partner_p2')}
                                                </p>

                                                <div className="mt-4 rounded-lg bg-cyan-50/80 p-4 dark:bg-cyan-900/20">
                                                    <p className="text-center text-sm leading-relaxed font-bold text-gray-800 dark:text-white/90">
                                                        {t('donation.banner')}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div className="flex items-center gap-2 text-xs text-gray-700 dark:text-white/70">
                                                    <Shield className="h-3 w-3" />
                                                    <span>
                                                        {t(
                                                            'donation.trust_line',
                                                        )}
                                                    </span>
                                                </div>

                                                <Button
                                                    size="lg"
                                                    className="rounded-full border-0 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400 px-8 py-5 font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-emerald-600 hover:via-teal-500 hover:to-cyan-500 hover:shadow-xl hover:shadow-emerald-500/25"
                                                >
                                                    <Heart className="mr-2 h-5 w-5" />
                                                    {t('donation.cta')}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="my-8 border-t border-gray-300/80 dark:border-white/10" />

                                    <div className="mb-8">
                                        <div className="mb-8 text-center">
                                            <div className="mb-3 flex items-center justify-center gap-3">
                                                <Video className="h-6 w-6 text-gray-900/90 dark:text-white" />
                                                <h2 className="bg-gradient-to-r from-purple-700 via-gray-900 to-cyan-700 bg-clip-text text-3xl font-bold text-transparent dark:from-purple-300 dark:via-white dark:to-cyan-300">
                                                    {t('clip_process.title')}
                                                </h2>
                                            </div>
                                            <p className="mx-auto max-w-3xl text-base text-gray-800 dark:text-white/90">
                                                {t('clip_process.intro')}
                                            </p>
                                        </div>

                                        <div className="mb-6 grid gap-4 md:grid-cols-2">
                                            {clipProcess.map((step, idx) => (
                                                <div
                                                    key={idx}
                                                    className="rounded-xl border border-gray-300/80 bg-white/65 p-5 transition-transform duration-200 hover:scale-[1.02] dark:border-white/10 dark:bg-black/25"
                                                >
                                                    <div className="mb-3 flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300/80 bg-white/60 dark:border-white/20 dark:bg-black/20">
                                                            <step.icon className="h-5 w-5 text-gray-900/90 dark:text-white" />
                                                        </div>
                                                        <h3 className="text-lg font-bold text-gray-900/90 dark:text-white/90">
                                                            {step.title}
                                                        </h3>
                                                    </div>
                                                    <p className="text-sm text-gray-800 dark:text-white/90">
                                                        {step.description}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>

                                        <div className="mb-6 rounded-xl border border-gray-300/80 bg-white/65 p-5 dark:border-white/10 dark:bg-black/25">
                                            <p className="text-base leading-relaxed text-gray-800 dark:text-white/90">
                                                {t('clip_process.neutrality')}
                                            </p>
                                        </div>

                                        <div className="rounded-xl border border-red-300 bg-red-50/80 p-5 dark:border-red-400/30 dark:bg-red-900/10">
                                            <div className="flex items-start gap-3">
                                                <Shield className="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-300" />
                                                <p className="text-sm leading-relaxed text-gray-800 dark:text-white/90">
                                                    {t(
                                                        'clip_process.blacklist',
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <Card className="rounded-2xl border border-gray-200 bg-gradient-to-br from-white/70 via-white/85 to-white/70 shadow-2xl ring-1 shadow-black/10 ring-black/5 backdrop-blur-xl dark:border-white/20 dark:bg-black/30 dark:!bg-none dark:!from-transparent dark:!via-transparent dark:!to-transparent dark:ring-0 dark:shadow-purple-900/30">
                            <CardContent className="p-6">
                                <div className="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                    <div>
                                        <span className="text-xs tracking-widest text-gray-700 uppercase dark:text-white/70">
                                            {t('video.latest_label')}
                                        </span>
                                    </div>
                                    <a
                                        href={youtubeUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Button
                                            size="sm"
                                            className="rounded-full border border-purple-300/60 bg-gradient-to-r from-purple-100/90 to-cyan-100/80 px-4 py-2 text-purple-800 hover:border-purple-400/70 hover:from-purple-200 hover:to-cyan-200 hover:text-purple-900 dark:border-purple-400/30 dark:bg-gradient-to-r dark:from-purple-500/20 dark:via-transparent dark:to-cyan-500/20 dark:text-white/90 dark:hover:border-purple-400/50 dark:hover:from-purple-500/30 dark:hover:to-cyan-500/30"
                                        >
                                            {t('video.watch')}
                                        </Button>
                                    </a>
                                </div>

                                <div className="relative aspect-video overflow-hidden rounded-xl border border-gray-300/80 bg-white/85 dark:border-white/15 dark:bg-black/40">
                                    <iframe
                                        width="100%"
                                        height="100%"
                                        src="https://www.youtube-nocookie.com/embed/videoseries?si=RE61OJQKY5oqgog4&amp;list=UUgZpwegd4AdDlZNrIamIgRw"
                                        title="YouTube video player"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerPolicy="strict-origin-when-cross-origin"
                                        allowFullScreen
                                    ></iframe>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </main>
                <div className="relative z-50 mt-auto">
                    <div
                        className={`border-t shadow-[0_-8px_30px_rgba(0,0,0,0.08)] backdrop-blur-md ${
                            isDarkMode
                                ? 'border-white/10 bg-black/35'
                                : 'border-black/10 bg-white/75'
                        }`}
                    >
                        <div
                            className={
                                isDarkMode
                                    ? '!text-white/85 [&_*]:!text-white/85 [&_a:hover]:!text-white [&_svg]:!text-white/85'
                                    : '!text-gray-900/85 [&_*]:!text-gray-900/85 [&_a:hover]:!text-gray-950 [&_svg]:!text-gray-900/85'
                            }
                        >
                            <Footer />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
