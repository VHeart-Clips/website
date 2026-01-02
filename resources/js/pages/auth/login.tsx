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
            if (e.key === 'appearance') {
                checkTheme();
            }
        };

        window.addEventListener('storage', handleStorageChange);

        const interval = setInterval(checkTheme, 1000);

        return () => {
            window.removeEventListener('storage', handleStorageChange);
            clearInterval(interval);
        };
    }, []);

    const initStars = useCallback((w: number, h: number, darkMode: boolean) => {
        const stars: Star[] = [];
        const starCount = Math.min(200, w / 8);

        for (let i = 0; i < starCount; i++) {
            stars.push({
                x: Math.random() * w,
                y: Math.random() * h,
                size: Math.random() * 2 + 0.5,
                speed: darkMode
                    ? Math.random() * 0.3 + 0.1
                    : Math.random() * 0.18 + 0.05,
                brightness: Math.random() * 0.6 + 0.4,
                pulseSpeed: Math.random() * 0.01 + 0.005,
                twinkle: Math.random() * Math.PI * 2,
            });
        }
        return stars;
    }, []);

    const initNebulas = useCallback(
        (w: number, h: number, darkMode: boolean) => {
            const nebulas: Nebula[] = [];
            const colors = darkMode
                ? [
                      'rgba(145, 70, 255, 0.1)',
                      'rgba(0, 174, 255, 0.07)',
                      'rgba(255, 70, 145, 0.05)',
                  ]
                : [
                      'rgba(145, 70, 255, 0.06)',
                      'rgba(0, 174, 255, 0.05)',
                      'rgba(255, 70, 145, 0.04)',
                  ];

            for (let i = 0; i < 4; i++) {
                nebulas.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    radius: Math.random() * 150 + 100,
                    speedX: Math.random() * 0.08 - 0.04,
                    speedY: Math.random() * 0.08 - 0.04,
                    color: colors[Math.floor(Math.random() * colors.length)],
                });
            }
            return nebulas;
        },
        [],
    );

    const ensureShootingPool = useCallback(() => {
        if (shootingPoolRef.current.length) return;
        const pool: ShootingStar[] = Array.from({ length: 4 }, () => ({
            active: false,
            x: 0,
            y: 0,
            vx: 0,
            vy: 0,
            life: 0,
            trail: [],
        }));
        shootingPoolRef.current = pool;
    }, []);

    const spawnShootingStar = useCallback(
        (w: number, h: number, darkMode: boolean) => {
            const pool = shootingPoolRef.current;
            const s = pool.find((p) => !p.active);
            if (!s) return;

            s.active = true;
            s.x = Math.random() * w;
            s.y = 0;
            const speed = darkMode ? 15 : 10;
            s.vx = -speed * 0.7;
            s.vy = speed;
            s.life = 1;
            s.trail = [];
        },
        [],
    );

    const setCanvasSize = useCallback(
        (
            canvas: HTMLCanvasElement,
            ctx: CanvasRenderingContext2D,
            darkMode: boolean,
        ) => {
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

            starsRef.current = initStars(cssW, cssH, darkMode);
            nebulasRef.current = initNebulas(cssW, cssH, darkMode);
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
                setCanvasSize(canvas, ctx, isDarkMode);
            });
        };

        const handleVisibilityChange = () => {
            isVisibleRef.current = document.visibilityState === 'visible';
        };

        setCanvasSize(canvas, ctx, isDarkMode);
        window.addEventListener('resize', onResize, { passive: true });
        document.addEventListener('visibilitychange', handleVisibilityChange);

        const drawPlanet = (
            x: number,
            y: number,
            radius: number,
            time: number,
            darkMode: boolean,
        ) => {
            ctx.save();
            ctx.translate(x, y);

            const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, radius);
            if (darkMode) {
                gradient.addColorStop(0, 'rgba(100, 65, 165, 0.8)');
                gradient.addColorStop(0.6, 'rgba(70, 35, 135, 0.6)');
                gradient.addColorStop(1, 'rgba(40, 20, 80, 0.4)');
            } else {
                gradient.addColorStop(0, 'rgba(155, 120, 220, 0.75)');
                gradient.addColorStop(0.6, 'rgba(130, 95, 200, 0.55)');
                gradient.addColorStop(1, 'rgba(110, 80, 180, 0.35)');
            }

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
            if (darkMode) {
                ringGradient.addColorStop(0, 'rgba(145, 70, 255, 0)');
                ringGradient.addColorStop(0.3, 'rgba(145, 70, 255, 0.3)');
                ringGradient.addColorStop(0.7, 'rgba(145, 70, 255, 0.3)');
                ringGradient.addColorStop(1, 'rgba(145, 70, 255, 0)');
            } else {
                ringGradient.addColorStop(0, 'rgba(145, 70, 255, 0)');
                ringGradient.addColorStop(0.3, 'rgba(145, 70, 255, 0.18)');
                ringGradient.addColorStop(0.7, 'rgba(145, 70, 255, 0.18)');
                ringGradient.addColorStop(1, 'rgba(145, 70, 255, 0)');
            }

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
            const darkMode = isDarkMode;

            const dt = Math.min(32, ts - lastFrameTimeRef.current);
            lastFrameTimeRef.current = ts;
            timeRef.current += dt * 0.001;

            ctx.fillStyle = darkMode ? '#0a0a1a' : '#EEF2F8';
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
                drawPlanet(w * 0.8, h * 0.2, 60, timeRef.current, darkMode);
                drawPlanet(
                    w * 0.2,
                    h * 0.7,
                    40,
                    timeRef.current * 1.2,
                    darkMode,
                );
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

                const starAlpha = darkMode ? s.brightness : s.brightness * 0.35;
                const starColor = darkMode ? 255 : 80;

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
                if (Math.random() < 0.02) spawnShootingStar(w, h, darkMode);
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
                    const trailColor = darkMode ? 255 : 90;

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
                    grad.addColorStop(
                        1,
                        darkMode
                            ? `rgba(180, 220, 255, ${alpha * 0.5})`
                            : `rgba(120, 150, 180, ${alpha * 0.45})`,
                    );

                    ctx.strokeStyle = grad;
                    ctx.lineWidth = 2 * (1 - j / s.trail.length);
                    ctx.lineCap = 'round';
                    ctx.stroke();
                }

                const headColor = darkMode ? 255 : 90;
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
                headGradient.addColorStop(
                    1,
                    darkMode
                        ? 'rgba(180, 220, 255, 0)'
                        : 'rgba(120, 150, 180, 0)',
                );
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

    const getBackgroundGradients = () => {
        if (isDarkMode) {
            return {
                background: 'bg-[#0a0a1a]',
                overlay: 'from-[#0a0a1a]/90 via-transparent to-[#0a0a1a]/80',
                radialGradients: `radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.15) 0%, transparent 50%),
                                  radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.1) 0%, transparent 50%)`,
            };
        } else {
            return {
                background: 'bg-[#F5F7FB]',
                overlay: 'from-[#F5F7FB]/85 via-[#F5F7FB]/60 to-[#E3E8F2]',
                radialGradients: ` radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.1) 0%, transparent 55%),
                                   radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.08) 0%, transparent 55%)`,
            };
        }
    };

    const gradients = getBackgroundGradients();

    return (
        <div
            className={`relative flex min-h-screen flex-col overflow-hidden ${gradients.background}`}
        >
            <canvas ref={canvasRef} className="absolute inset-0" />

            <div
                className={`absolute inset-0 bg-gradient-to-t ${gradients.overlay}`}
            />

            <div
                className="absolute inset-0"
                style={{
                    backgroundImage: gradients.radialGradients,
                }}
            />

            <main className="relative z-20 flex flex-1 flex-col items-center justify-center p-4 pb-16">
                <Card
                    className={`w-full max-w-md ${
                        isDarkMode
                            ? 'border-white/20 bg-gradient-to-br from-black/40 via-black/30 to-black/40 shadow-2xl shadow-purple-900/30'
                            : 'border-black/10 bg-gradient-to-br from-white/80 via-white/90 to-white/80 shadow-2xl ring-1 shadow-black/10 ring-black/5'
                    } backdrop-blur-xl`}
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
                                            : 'drop-shadow-[0_0_30px_rgba(145,70,255,0.25)]'
                                    }`}
                                />
                                <div
                                    className={`absolute inset-0 rounded-full ${
                                        isDarkMode
                                            ? 'bg-purple-500/30'
                                            : 'bg-purple-400/12'
                                    } blur-2xl`}
                                />
                                <div
                                    className={`absolute -inset-4 animate-pulse rounded-full border-2 ${
                                        isDarkMode
                                            ? 'border-purple-500/20'
                                            : 'border-purple-400/15'
                                    }`}
                                />
                            </div>
                        </div>

                        <CardTitle className="text-4xl font-bold tracking-tight">
                            <span
                                className={`bg-gradient-to-r ${
                                    isDarkMode
                                        ? 'from-purple-300 via-white to-cyan-300'
                                        : 'from-purple-700 via-gray-900 to-cyan-700'
                                } bg-clip-text text-transparent`}
                            >
                                {t('title')}
                            </span>
                        </CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        <p
                            className={`text-center text-lg leading-relaxed ${
                                isDarkMode ? 'text-white/90' : 'text-gray-800'
                            }`}
                        >
                            {t('description')}
                        </p>

                        <div className="flex justify-center">
                            <div
                                className={`h-[1px] w-32 bg-gradient-to-r from-transparent ${
                                    isDarkMode
                                        ? 'via-white/50'
                                        : 'via-gray-700/40'
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
                                    className={`absolute -inset-1 rounded-lg bg-gradient-to-r ${
                                        isDarkMode
                                            ? 'from-purple-600 to-cyan-500 opacity-60'
                                            : 'from-purple-600 to-cyan-500 opacity-35'
                                    } blur-xl transition-opacity duration-300 group-hover:opacity-60`}
                                />
                                <Button
                                    className={`relative w-full border-0 bg-gradient-to-r py-7 text-lg shadow-2xl transition-all duration-300 ${
                                        isDarkMode
                                            ? 'from-purple-700 via-purple-600 to-cyan-600 group-hover:shadow-purple-500/30 hover:from-purple-800 hover:to-cyan-700'
                                            : 'from-purple-600 via-purple-500 to-cyan-500 group-hover:shadow-black/10 hover:from-purple-700 hover:to-cyan-600'
                                    }`}
                                    size="lg"
                                >
                                    <div className="flex items-center justify-center space-x-3">
                                        <div className="relative">
                                            <TwitchIcon className="h-7 w-7 text-white" />
                                            <div
                                                className={`absolute inset-0 ${
                                                    isDarkMode
                                                        ? 'bg-cyan-400/40'
                                                        : 'bg-cyan-400/18'
                                                } blur-md`}
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
                            className={`border-t ${
                                isDarkMode
                                    ? 'border-white/20 text-white/70'
                                    : 'border-black/10 text-gray-700'
                            } pt-4 text-center text-sm`}
                        >
                            {t('terms_notice')}
                        </p>
                    </CardFooter>
                </Card>

                <div className="mt-10 text-center">
                    <p
                        className={`rounded-2xl border ${
                            isDarkMode
                                ? 'border-white/20 bg-white/10 text-white/90'
                                : 'border-black/10 bg-white/70 text-gray-800'
                        } px-6 py-3 backdrop-blur-sm`}
                    >
                        {t('community_support')}
                        <span
                            className={`ml-2 animate-pulse ${
                                isDarkMode ? 'text-cyan-300' : 'text-cyan-600'
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
                            : 'border-black/10 bg-white/85'
                    }`}
                >
                    <div
                        className={
                            isDarkMode
                                ? '!text-white/85 [&_*]:!text-white/85 [&_a:hover]:!text-white [&_svg]:!text-white/85'
                                : '!text-gray-800 [&_*]:!text-gray-800 [&_a:hover]:!text-gray-950 [&_svg]:!text-gray-800'
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
