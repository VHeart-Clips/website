import Footer from '@/components/footer/footer';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { CheckCircle, Heart, LogIn, Shield, Sparkles, Users, Video, Vote } from 'lucide-react';
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

export default function Welcome() {
    const { t } = useTranslation('welcome');

    const [theme, setTheme] = useState<'dark' | 'light'>('dark');
    const [isMobile, setIsMobile] = useState(false);

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
    const videoId =
        youtubeUrl.match(
            /(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&?]+)/,
        )?.[1] || '';
    const embedUrl = `https://www.youtube-nocookie.com/embed/${videoId}?rel=0&modestbranding=1`;

    useEffect(() => {
        const checkMobile = () => setIsMobile(window.innerWidth < 768);
        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    useEffect(() => {
        const getTheme = (): 'dark' | 'light' => {
            if (typeof window !== 'undefined') {
                const saved = localStorage.getItem('appearance');
                if (saved === 'light') return 'light';
                if (saved === 'dark') return 'dark';
            }

            const root = document.documentElement;
            if (root.classList.contains('dark')) return 'dark';
            if (root.classList.contains('light')) return 'light';

            return window.matchMedia?.('(prefers-color-scheme: dark)')?.matches
                ? 'dark'
                : 'light';
        };

        const applyTheme = () => setTheme(getTheme());
        applyTheme();

        const mql = window.matchMedia?.('(prefers-color-scheme: dark)');
        const onMql = () => applyTheme();
        mql?.addEventListener?.('change', onMql);

        const mo = new MutationObserver(() => applyTheme());
        mo.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        const handleStorageChange = (e: StorageEvent) => {
            if (e.key === 'appearance') applyTheme();
        };
        window.addEventListener('storage', handleStorageChange);

        return () => {
            mql?.removeEventListener?.('change', onMql);
            mo.disconnect();
            window.removeEventListener('storage', handleStorageChange);
        };
    }, []);

    const initStars = useCallback(
        (w: number, h: number) => {
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
                        theme === 'dark'
                            ? Math.random() * 0.3 + 0.1
                            : Math.random() * 0.18 + 0.05,
                    brightness: Math.random() * 0.6 + 0.4,
                    pulseSpeed: Math.random() * 0.01 + 0.005,
                    twinkle: Math.random() * Math.PI * 2,
                });
            }
            return stars;
        },
        [theme, isMobile],
    );

    const initNebulas = useCallback(
        (w: number, h: number) => {
            const colors =
                theme === 'dark'
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

            return Array.from({ length: 4 }).map(() => ({
                x: Math.random() * w,
                y: Math.random() * h,
                radius: Math.random() * 150 + 100,
                speedX: Math.random() * 0.08 - 0.04,
                speedY: Math.random() * 0.08 - 0.04,
                color: colors[Math.floor(Math.random() * colors.length)],
            }));
        },
        [theme],
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
            const darkMode = theme === 'dark';
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
        [theme],
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
            const darkMode = theme === 'dark';

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

                ctx.fillStyle = darkMode
                    ? 'rgba(255,255,255,0.9)'
                    : 'rgba(90,90,90,0.9)';
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
    }, [ensureShootingPool, setCanvasSize, spawnShootingStar, theme]);

    const gradients =
        theme === 'dark'
            ? {
                  background: 'bg-[#0a0a1a]',
                  overlay: 'from-[#0a0a1a]/90 via-transparent to-[#0a0a1a]/80',
                  radial: `radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.15) 0%, transparent 50%),
                           radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.1) 0%, transparent 50%)`,
              }
            : {
                  background: 'bg-[#F5F7FB]',
                  overlay: 'from-[#F5F7FB]/85 via-[#F5F7FB]/60 to-[#E3E8F2]',
                  radial: `radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.1) 0%, transparent 55%),
                           radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.08) 0%, transparent 55%)`,
              };

    const ui =
        theme === 'dark'
            ? {
                  cardShell:
                      'border border-white/20 bg-black/30 backdrop-blur-xl',
                  cardText: 'text-white/90',
                  muted: 'text-white/70',
                  titleGrad: 'from-purple-300 via-white to-cyan-300',
                  login: 'bg-gradient-to-r from-purple-500/20 via-transparent to-cyan-500/20 border border-purple-400/30 text-white/90 hover:from-purple-500/30 hover:to-cyan-500/30 hover:border-purple-400/50',
                  videoBorder: 'border-white/15 bg-black/40',
                  divider: 'border-white/10',
                  iconBox: 'border-white/20 bg-black/20',
                  innerCard: 'border-white/15 bg-black/20',
                  infoCard: 'border-white/10 bg-black/25',
                  donateButton:
                      'bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 hover:from-purple-700 hover:via-pink-600 hover:to-red-600 text-white shadow-lg hover:shadow-purple-500/25',
                  tag: 'border-white/15 bg-gradient-to-r from-purple-500/20 to-cyan-500/20 text-white/85',
              }
            : {
                  cardShell:
                      'border border-black/10 bg-gradient-to-br from-white/80 via-white/90 to-white/80 ring-1 ring-black/5 backdrop-blur-xl',
                  cardText: 'text-gray-800',
                  muted: 'text-gray-700',
                  titleGrad: 'from-purple-700 via-gray-900 to-cyan-700',
                  login: 'bg-gradient-to-r from-purple-100 to-cyan-100 border border-purple-300/50 text-purple-700 hover:from-purple-200 hover:to-cyan-200 hover:border-purple-400/70 hover:text-purple-800',
                  videoBorder: 'border-black/10 bg-white/85',
                  divider: 'border-black/10',
                  iconBox: 'border-black/10 bg-white/60',
                  innerCard: 'border-black/10 bg-white/60',
                  infoCard: 'border-black/10 bg-white/70',
                  donateButton:
                      'bg-gradient-to-r from-purple-500 via-pink-400 to-red-400 hover:from-purple-600 hover:via-pink-500 hover:to-red-500 text-white shadow-lg hover:shadow-purple-400/25',
                  tag: 'border-black/10 bg-gradient-to-r from-purple-100 to-cyan-100 text-gray-800',
              };

    const titleGrad = `bg-gradient-to-r ${ui.titleGrad} bg-clip-text text-transparent`;
    const muted = `text-xs tracking-widest uppercase ${ui.muted}`;

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

            <div
                className={`relative flex min-h-screen flex-col overflow-hidden ${gradients.background}`}
            >
                <canvas
                    ref={canvasRef}
                    className="pointer-events-none fixed inset-0"
                    style={{ zIndex: 0 }}
                />

                <div
                    className={`fixed inset-0 bg-gradient-to-t ${gradients.overlay}`}
                    style={{ zIndex: 1 }}
                />

                <div
                    className="fixed inset-0"
                    style={{ backgroundImage: gradients.radial, zIndex: 1 }}
                />

                <main className="relative z-10 flex flex-1 items-center justify-center px-4 py-12">
                    <div className="w-full max-w-[1200px] space-y-8">
                        <div className="flex justify-end px-2">
                            <Link
                                href="/login"
                                className={`group inline-flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-medium transition-all duration-300 ${ui.login} hover:scale-105 hover:shadow-lg`}
                            >
                                <div className="relative">
                                    <LogIn className="h-4 w-4" />
                                    <Sparkles className="absolute -top-1 -right-1 h-2 w-2 text-cyan-300 opacity-0 transition-opacity group-hover:opacity-100" />
                                </div>
                                {t('login.members')}
                            </Link>
                        </div>

                        <Card className={`rounded-2xl ${ui.cardShell}`}>
                            <div className="px-6 py-8 sm:px-10 sm:py-12">
                                <div className="mx-auto max-w-5xl">
                                    <div className="mb-10 text-center">
                                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                            {t('hero.title_prefix')}{' '}
                                            <span className={titleGrad}>
                                                {t('hero.brand')}
                                            </span>
                                        </h1>

                                        <p
                                            className={`mx-auto mt-6 max-w-3xl text-base leading-relaxed sm:text-lg ${ui.cardText}`}
                                        >
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
                                                    className={`rounded-full border px-3 py-1.5 text-sm font-medium ${ui.tag}`}
                                                >
                                                    {tag}
                                                </span>
                                            ))}
                                        </div>
                                    </div>

                                    <div
                                        className={`border-t ${ui.divider} my-8`}
                                    />

                                    <div className="mb-10 grid gap-8 lg:grid-cols-2">
                                        <div className="space-y-6">
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className={`rounded-xl p-2.5 ${ui.iconBox}`}
                                                >
                                                    <Users className="h-6 w-6" />
                                                </div>
                                                <h2
                                                    className={`text-2xl font-bold ${titleGrad}`}
                                                >
                                                    {t('about.title')}
                                                </h2>
                                            </div>

                                            <p
                                                className={`text-base leading-relaxed ${ui.cardText}`}
                                            >
                                                {t('about.p1')}
                                            </p>

                                            <p
                                                className={`text-base leading-relaxed ${ui.cardText}`}
                                            >
                                                {t('about.p2')}
                                            </p>

                                            <div
                                                className={`rounded-xl p-4 ${ui.innerCard} mt-4`}
                                            >
                                                <p
                                                    className={`text-sm ${ui.cardText} font-bold`}
                                                >
                                                    {t('about.hashtag')}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            className={`rounded-xl border p-6 ${ui.innerCard}`}
                                        >
                                            <div className="mb-4 flex items-start gap-4">
                                                <div
                                                    className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${ui.iconBox}`}
                                                >
                                                    <Heart className="h-6 w-6" />
                                                </div>

                                                <div>
                                                    <h3
                                                        className={`text-2xl font-bold ${titleGrad} mb-2`}
                                                    >
                                                        {t('donation.title')}
                                                    </h3>
                                                    <p
                                                        className={`text-base leading-relaxed ${ui.cardText}`}
                                                    >
                                                        {t('donation.intro')}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="mb-6 space-y-4">
                                                <div
                                                    className={`rounded-lg p-4 ${theme === 'dark' ? 'bg-purple-900/20' : 'bg-purple-50'}`}
                                                >
                                                    <p
                                                        className={`text-sm leading-relaxed ${ui.cardText}`}
                                                    >
                                                        <span className="font-bold">
                                                            {t(
                                                                'donation.hashtag',
                                                            )}
                                                        </span>
                                                    </p>
                                                </div>

                                                <p
                                                    className={`text-sm leading-relaxed ${ui.cardText}`}
                                                >
                                                    {t('donation.partner_p1', {
                                                        partner: t(
                                                            'donation.partner_placeholder',
                                                        ),
                                                    })}
                                                </p>

                                                <p
                                                    className={`text-sm leading-relaxed ${ui.cardText}`}
                                                >
                                                    {t('donation.partner_p2')}
                                                </p>

                                                <div
                                                    className={`rounded-lg p-4 ${theme === 'dark' ? 'bg-cyan-900/20' : 'bg-cyan-50'} mt-4`}
                                                >
                                                    <p
                                                        className={`text-sm leading-relaxed ${ui.cardText} text-center font-bold`}
                                                    >
                                                        {t('donation.banner')}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div
                                                    className={`text-xs ${ui.muted} flex items-center gap-2`}
                                                >
                                                    <Shield className="h-3 w-3" />
                                                    <span>
                                                        {t(
                                                            'donation.trust_line',
                                                        )}
                                                    </span>
                                                </div>

                                                <Button
                                                    size="lg"
                                                    className={`rounded-full border-0 px-8 py-5 font-bold transition-all duration-300 hover:scale-105 hover:shadow-xl ${ui.donateButton}`}
                                                >
                                                    <Heart className="mr-2 h-5 w-5" />
                                                    {t('donation.cta')}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        className={`border-t ${ui.divider} my-8`}
                                    />

                                    <div className="mb-8">
                                        <div className="mb-8 text-center">
                                            <div className="mb-3 flex items-center justify-center gap-3">
                                                <Video className="h-6 w-6" />
                                                <h2
                                                    className={`text-3xl font-bold ${titleGrad}`}
                                                >
                                                    {t('clip_process.title')}
                                                </h2>
                                            </div>
                                            <p
                                                className={`mx-auto max-w-3xl text-base ${ui.cardText}`}
                                            >
                                                {t('clip_process.intro')}
                                            </p>
                                        </div>

                                        <div className="mb-6 grid gap-4 md:grid-cols-2">
                                            {clipProcess.map((step, idx) => (
                                                <div
                                                    key={idx}
                                                    className={`rounded-xl border p-5 ${ui.infoCard} transition-transform duration-200 hover:scale-[1.02]`}
                                                >
                                                    <div className="mb-3 flex items-center gap-3">
                                                        <div
                                                            className={`flex h-10 w-10 items-center justify-center rounded-full ${ui.iconBox}`}
                                                        >
                                                            <step.icon className="h-5 w-5" />
                                                        </div>
                                                        <h3
                                                            className={`text-lg font-bold ${ui.cardText}`}
                                                        >
                                                            {step.title}
                                                        </h3>
                                                    </div>
                                                    <p
                                                        className={`text-sm ${ui.cardText}`}
                                                    >
                                                        {step.description}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>

                                        <div
                                            className={`rounded-xl border p-5 ${ui.infoCard} mb-6`}
                                        >
                                            <p
                                                className={`text-base leading-relaxed ${ui.cardText}`}
                                            >
                                                {t('clip_process.neutrality')}
                                            </p>
                                        </div>

                                        <div
                                            className={`rounded-xl border p-5 ${theme === 'dark' ? 'border-red-400/30 bg-red-900/10' : 'border-red-300 bg-red-50'}`}
                                        >
                                            <div className="flex items-start gap-3">
                                                <Shield
                                                    className={`mt-0.5 h-5 w-5 flex-shrink-0 ${theme === 'dark' ? 'text-red-300' : 'text-red-600'}`}
                                                />
                                                <p
                                                    className={`text-sm leading-relaxed ${ui.cardText}`}
                                                >
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

                        <Card className={`rounded-2xl ${ui.cardShell}`}>
                            <CardContent className="p-6">
                                <div className="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                    <div>
                                        <span className={muted}>
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
                                            className={`rounded-full border px-4 py-2 ${ui.login}`}
                                        >
                                            {t('video.watch')}
                                        </Button>
                                    </a>
                                </div>

                                <div
                                    className={`relative aspect-video overflow-hidden rounded-xl border ${ui.videoBorder}`}
                                >
                                    <iframe
                                        src={embedUrl}
                                        title={t('video.iframe_title')}
                                        allow="autoplay; encrypted-media; picture-in-picture"
                                        allowFullScreen
                                        className="absolute inset-0 h-full w-full"
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </main>

                <div className="relative z-50 mt-auto">
                    <div
                        className={`border-t backdrop-blur-md ${
                            theme === 'dark'
                                ? 'border-white/10 bg-black/35'
                                : 'border-black/10 bg-white/85'
                        }`}
                    >
                        <div
                            className={
                                theme === 'dark'
                                    ? '!text-white/85 [&_*]:!text-white/85 [&_a:hover]:!text-white [&_svg]:!text-white/85'
                                    : '!text-gray-800 [&_*]:!text-gray-800 [&_a:hover]:!text-gray-950 [&_svg]:!text-gray-800'
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
