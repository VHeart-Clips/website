import Footer from '@/components/footer/footer';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Logo from '/resources/images/svg/logo-dark.svg';
import LogoLight from '/resources/images/svg/logo-light.svg';

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
        headColor: 255,
        trailColor: 255,
        starSpeedMin: 0.1,
        starSpeedMax: 0.4,
        shootingStarSpeed: 15,
        headGradientEnd: 'rgba(180, 220, 255, 0)',
        trailGradientEnd: 'rgba(180, 220, 255, 0.5)',
    },
    light: {
        background: '#DDE4F1',
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
            'rgba(145, 70, 255, 0.10)',
            'rgba(0, 174, 255, 0.09)',
            'rgba(255, 70, 145, 0.07)',
        ],
        starColor: 90,
        starAlphaMultiplier: 0.4,
        shootingStarColor: 'rgba(90,90,90,0.85)',
        headColor: 95,
        trailColor: 95,
        starSpeedMin: 0.05,
        starSpeedMax: 0.23,
        shootingStarSpeed: 10,
        headGradientEnd: 'rgba(120, 150, 180, 0)',
        trailGradientEnd: 'rgba(120, 150, 180, 0.45)',
    },
};

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

export default function Welcome({
    kannRegistrieren = true,
}: {
    kannRegistrieren?: boolean;
}) {
    const { t } = useTranslation('login');
    const twitchAuthUrl = '/auth/twitch';

    const [isDarkMode, setIsDarkMode] = useState(true);

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

    useEffect(() => {
        const checkTheme = () => {
            const theme = localStorage.getItem('appearance');
            setIsDarkMode(theme !== 'light');
        };

        checkTheme();

        const handleStorageChange = (e: StorageEvent) => {
            if (e.key === 'appearance') checkTheme();
        };

        window.addEventListener('storage', handleStorageChange);
        const interval = setInterval(checkTheme, 1000);

        return () => {
            window.removeEventListener('storage', handleStorageChange);
            clearInterval(interval);
        };
    }, []);

    const initStars = useCallback(
        (w: number, h: number) => {
            const canvasTheme = CANVAS_THEMES[isDarkMode ? 'dark' : 'light'];
            const stars: Star[] = [];
            const starCount = Math.min(200, w / 8);

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
        [isDarkMode],
    );

    const initNebulas = useCallback(
        (w: number, h: number) => {
            const canvasTheme = CANVAS_THEMES[isDarkMode ? 'dark' : 'light'];
            const nebulas: Nebula[] = [];

            for (let i = 0; i < 4; i++) {
                nebulas.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    radius: Math.random() * 150 + 100,
                    speedX: Math.random() * 0.08 - 0.04,
                    speedY: Math.random() * 0.08 - 0.04,
                    color: canvasTheme.nebulaColors[
                        Math.floor(
                            Math.random() * canvasTheme.nebulaColors.length,
                        )
                    ],
                });
            }
            return nebulas;
        },
        [isDarkMode],
    );

    const ensureShootingPool = useCallback(() => {
        if (shootingPoolRef.current.length) return;

        shootingPoolRef.current = Array.from({ length: 4 }, () => ({
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
            const canvasTheme = CANVAS_THEMES[isDarkMode ? 'dark' : 'light'];
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
            const canvasTheme = CANVAS_THEMES[isDarkMode ? 'dark' : 'light'];

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
            const canvasTheme = CANVAS_THEMES[isDarkMode ? 'dark' : 'light'];

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

                s.trail.push({ x: s.x, y: s.y, life: s.life });
                if (s.trail.length > 5) s.trail.shift();

                if (s.life <= 0 || s.y > h + 50) {
                    s.active = false;
                    continue;
                }

                for (let j = 0; j < s.trail.length; j++) {
                    const point = s.trail[j];
                    const alpha = point.life * (j / s.trail.length) * 0.8;
                    const trailColor = canvasTheme.trailColor;

                    ctx.beginPath();
                    if (j === 0) {
                        ctx.moveTo(point.x, point.y);
                    } else {
                        const prev = s.trail[j - 1];
                        ctx.moveTo(prev.x, prev.y);
                        ctx.lineTo(point.x, point.y);
                    }

                    const grad = ctx.createLinearGradient(
                        point.x,
                        point.y,
                        point.x - s.vx * 0.5,
                        point.y - s.vy * 0.5,
                    );
                    grad.addColorStop(
                        0,
                        `rgba(${trailColor}, ${trailColor}, ${trailColor}, ${alpha})`,
                    );
                    grad.addColorStop(1, canvasTheme.trailGradientEnd);

                    ctx.strokeStyle = grad;
                    ctx.lineWidth = 2 * (1 - j / s.trail.length);
                    ctx.lineCap = 'round';
                    ctx.stroke();
                }

                const headColor = canvasTheme.headColor;
                const headGradient = ctx.createRadialGradient(
                    s.x,
                    s.y,
                    0,
                    s.x,
                    s.y,
                    6,
                );
                headGradient.addColorStop(
                    0,
                    `rgba(${headColor}, ${headColor}, ${headColor}, 0.9)`,
                );
                headGradient.addColorStop(1, canvasTheme.headGradientEnd);
                ctx.fillStyle = headGradient;
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

    return (
        <div
            className={`relative flex min-h-screen flex-col overflow-hidden ${
                isDarkMode ? 'bg-[#0a0a1a]' : 'bg-[#DDE4F1]'
            }`}
        >
            <canvas ref={canvasRef} className="absolute inset-0" />

            <div
                className={`absolute inset-0 bg-gradient-to-t ${
                    isDarkMode
                        ? 'from-[#0a0a1a]/90 via-transparent to-[#0a0a1a]/80'
                        : 'from-[#C9D3E7]/65 via-[#DDE4F1]/40 to-[#C9D3E7]/55'
                }`}
            />

            <div
                className={`absolute inset-0 ${
                    isDarkMode
                        ? 'bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.15)_0%,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.1)_0%,transparent_50%)]'
                        : 'bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.16)_0%,transparent_55%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.14)_0%,transparent_55%)]'
                }`}
            />

            <main className="relative z-20 flex flex-1 flex-col items-center justify-center p-4 pb-16">
                <Card
                    className={`w-full max-w-md backdrop-blur-xl ${
                        isDarkMode
                            ? 'border-white/20 bg-gradient-to-br from-black/40 via-black/30 to-black/40 shadow-2xl shadow-purple-900/30'
                            : 'border-black/10 bg-gradient-to-br from-white/55 via-white/70 to-white/55 shadow-2xl ring-1 shadow-black/10 ring-black/5'
                    }`}
                >
                    <CardHeader className="space-y-6 text-center">
                        <div className="flex justify-center">
                            <div className="relative">
                                <img
                                    src={isDarkMode ? Logo : LogoLight}
                                    alt={t('logo_alt')}
                                    className={`h-24 w-24 ${
                                        isDarkMode
                                            ? 'drop-shadow-[0_0_40px_rgba(145,70,255,0.7)]'
                                            : 'drop-shadow-[0_0_30px_rgba(145,70,255,0.22)]'
                                    }`}
                                />
                                <div
                                    className={`absolute inset-0 rounded-full blur-2xl ${
                                        isDarkMode
                                            ? 'bg-purple-500/30'
                                            : 'bg-purple-500/14'
                                    }`}
                                />
                                <div
                                    className={`absolute -inset-4 animate-pulse rounded-full border-2 ${
                                        isDarkMode
                                            ? 'border-purple-500/20'
                                            : 'border-purple-500/14'
                                    }`}
                                />
                            </div>
                        </div>

                        <CardTitle className="text-4xl font-bold tracking-tight">
                            <span
                                className={`bg-gradient-to-r bg-clip-text text-transparent ${
                                    isDarkMode
                                        ? 'from-purple-300 via-white to-cyan-300'
                                        : 'from-purple-700 via-gray-900 to-cyan-700'
                                }`}
                            >
                                {t('title')}
                            </span>
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        <p
                            className={`text-center text-lg leading-relaxed ${
                                isDarkMode
                                    ? 'text-white/90'
                                    : 'text-gray-900/85'
                            }`}
                        >
                            {t('description')}
                        </p>

                        <div className="flex justify-center">
                            <div
                                className={`h-[1px] w-32 bg-gradient-to-r from-transparent ${
                                    isDarkMode
                                        ? 'via-white/50'
                                        : 'via-gray-900/35'
                                } to-transparent`}
                            />
                        </div>
                    </CardContent>

                    <CardFooter className="flex flex-col space-y-6">
                        {kannRegistrieren && (
                            <a
                                href={twitchAuthUrl}
                                className="group relative w-full"
                                aria-label={t('connect_button_aria')}
                            >
                                <div
                                    className={`absolute -inset-1 rounded-lg bg-gradient-to-r from-purple-600 to-cyan-500 blur-xl transition-opacity duration-300 group-hover:opacity-60 ${
                                        isDarkMode ? 'opacity-60' : 'opacity-40'
                                    }`}
                                />
                                <Button
                                    className={`relative w-full border-0 bg-gradient-to-r py-7 text-lg shadow-2xl transition-all duration-300 ${
                                        isDarkMode
                                            ? 'from-purple-700 via-purple-600 to-cyan-600 group-hover:shadow-purple-500/30 hover:from-purple-800 hover:to-cyan-700'
                                            : 'from-purple-600 via-purple-500 to-cyan-500 group-hover:shadow-black/15 hover:from-purple-700 hover:to-cyan-600'
                                    }`}
                                    size="lg"
                                >
                                    <div className="flex items-center justify-center space-x-3">
                                        <div className="relative">
                                            <TwitchIcon className="h-7 w-7 text-white" />
                                            <div
                                                className={`absolute inset-0 blur-md ${
                                                    isDarkMode
                                                        ? 'bg-cyan-400/40'
                                                        : 'bg-cyan-500/22'
                                                }`}
                                            />
                                        </div>
                                        <span className="font-bold text-white drop-shadow-lg">
                                            {t('connect_button')}
                                        </span>
                                    </div>
                                </Button>
                            </a>
                        )}

                        <p
                            className={`border-t pt-4 text-center text-sm ${
                                isDarkMode
                                    ? 'border-white/20 text-white/70'
                                    : 'border-black/10 text-gray-800/70'
                            }`}
                        >
                            {t('terms_notice')}
                        </p>
                    </CardFooter>
                </Card>

                <div className="mt-10 text-center">
                    <p
                        className={`rounded-2xl border px-6 py-3 backdrop-blur-sm ${
                            isDarkMode
                                ? 'border-white/20 bg-white/10 text-white/90'
                                : 'border-black/10 bg-white/55 text-gray-900/85'
                        }`}
                    >
                        {t('community_support')}
                        <span
                            className={`ml-2 animate-pulse ${
                                isDarkMode ? 'text-cyan-300' : 'text-cyan-700'
                            }`}
                        >
                            ✦
                        </span>
                    </p>
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
    );
}

function TwitchIcon({ className }: { className?: string }) {
    return (
        <svg
            className={className}
            viewBox="0 0 24 24"
            fill="currentColor"
            aria-hidden="true"
        >
            <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z" />
        </svg>
    );
}
