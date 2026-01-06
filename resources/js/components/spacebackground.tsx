import { useCallback, useEffect, useRef, useState } from 'react';

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
        headColor: 255,
        trailColor: 255,
        starSpeedMin: 0.1,
        starSpeedMax: 0.4,
        shootingStarSpeed: 15,
        headGradientEnd: 'rgba(180, 220, 255, 0)',
        trailGradientEnd: 'rgba(180, 220, 255, 0.5)',
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
        headColor: 95,
        trailColor: 95,
        starSpeedMin: 0.05,
        starSpeedMax: 0.23,
        shootingStarSpeed: 10,
        headGradientEnd: 'rgba(120, 150, 180, 0)',
        trailGradientEnd: 'rgba(120, 150, 180, 0.45)',
    },
};

export default function SpaceBackground() {
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
    const isDarkModeRef = useRef(false);

    useEffect(() => {
        const checkTheme = () => {
            isDarkModeRef.current =
                document.documentElement.classList.contains('dark');
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

    const [isMobile, setIsMobile] = useState(false);

    useEffect(() => {
        const checkMobile = () => setIsMobile(window.innerWidth < 768);
        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    const getCanvasTheme = useCallback(() => {
        return isDarkModeRef.current ? CANVAS_THEMES.dark : CANVAS_THEMES.light;
    }, []);

    const initStars = useCallback(
        (w: number, h: number) => {
            const canvasTheme = getCanvasTheme();

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
        [getCanvasTheme, isMobile],
    );

    const initNebulas = useCallback(
        (w: number, h: number) => {
            const canvasTheme = getCanvasTheme();

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
        [getCanvasTheme],
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
            const canvasTheme = getCanvasTheme();

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
        [getCanvasTheme],
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
            const canvasTheme = getCanvasTheme();

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
            const canvasTheme = getCanvasTheme();

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
    }, [ensureShootingPool, setCanvasSize, spawnShootingStar, getCanvasTheme]);

    return (
        <>
            <canvas
                ref={canvasRef}
                className="pointer-events-none fixed inset-0"
                style={{ zIndex: 0 }}
            />
            <div
                className="fixed inset-0 bg-gradient-to-t from-blue-200/55 via-blue-100/30 to-blue-200/45 dark:from-[#0a0a1a]/90 dark:via-transparent dark:to-[#0a0a1a]/80"
                style={{ zIndex: 0 }}
            />
            <div
                className="fixed inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.12)_0%,transparent_55%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.10)_0%,transparent_55%)] dark:bg-[radial-gradient(circle_at_20%_30%,rgba(145,70,255,0.15)_0%,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(0,174,255,0.10)_0%,transparent_50%)]"
                style={{ zIndex: 0 }}
            />
        </>
    );
}
