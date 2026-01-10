import { useCallback, useEffect, useRef } from 'react';

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
            { offset: 0, color: 'rgba(100, 65, 165, 0.6)' },
            { offset: 0.6, color: 'rgba(70, 35, 135, 0.4)' },
            { offset: 1, color: 'rgba(40, 20, 80, 0.2)' },
        ],
        ringGradientStops: [
            { offset: 0, color: 'rgba(145, 70, 255, 0)' },
            { offset: 0.3, color: 'rgba(145, 70, 255, 0.15)' },
            { offset: 0.7, color: 'rgba(145, 70, 255, 0.15)' },
            { offset: 1, color: 'rgba(145, 70, 255, 0)' },
        ],
        nebulaColors: [
            'rgba(145, 70, 255, 0.05)',
            'rgba(0, 174, 255, 0.04)',
            'rgba(255, 70, 145, 0.03)',
        ],
        starColor: 255,
        starAlphaMultiplier: 1,
        shootingStarColor: 'rgba(255,255,255,0.8)',
        headColor: 255,
        trailColor: 255,
        starSpeedMin: 0.05,
        starSpeedMax: 0.2,
        shootingStarSpeed: 8,
        headGradientEnd: 'rgba(180, 220, 255, 0)',
        trailGradientEnd: 'rgba(180, 220, 255, 0.4)',
    },
    light: {
        background: '#EEF2F8',
        planetGradientStops: [
            { offset: 0, color: 'rgba(155, 120, 220, 0.5)' },
            { offset: 0.6, color: 'rgba(130, 95, 200, 0.3)' },
            { offset: 1, color: 'rgba(110, 80, 180, 0.1)' },
        ],
        ringGradientStops: [
            { offset: 0, color: 'rgba(145, 70, 255, 0)' },
            { offset: 0.3, color: 'rgba(145, 70, 255, 0.08)' },
            { offset: 0.7, color: 'rgba(145, 70, 255, 0.08)' },
            { offset: 1, color: 'rgba(145, 70, 255, 0)' },
        ],
        nebulaColors: [
            'rgba(145, 70, 255, 0.03)',
            'rgba(0, 174, 255, 0.02)',
            'rgba(255, 70, 145, 0.01)',
        ],
        starColor: 80,
        starAlphaMultiplier: 0.3,
        shootingStarColor: 'rgba(90,90,90,0.8)',
        headColor: 95,
        trailColor: 95,
        starSpeedMin: 0.03,
        starSpeedMax: 0.1,
        shootingStarSpeed: 5,
        headGradientEnd: 'rgba(120, 150, 180, 0)',
        trailGradientEnd: 'rgba(120, 150, 180, 0.35)',
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
    const planetCanvasesRef = useRef<HTMLCanvasElement[]>([]);
    const planetRotationsRef = useRef<number[]>([0, 0]);
    const planetDrawnRef = useRef<boolean[]>([false, false]);
    const frameCountRef = useRef(0);
    const performanceTierRef = useRef<'low' | 'medium' | 'high'>('medium');

    const FPS_LIMIT = 30;
    const FRAME_INTERVAL = 1000 / FPS_LIMIT;

    useEffect(() => {
        const detectPerformanceTier = () => {
            const cores = navigator.hardwareConcurrency || 4;

            if (cores <= 4 || window.innerWidth < 1024) {
                performanceTierRef.current = 'low';
            } else if (cores <= 8) {
                performanceTierRef.current = 'medium';
            } else {
                performanceTierRef.current = 'high';
            }
        };

        detectPerformanceTier();

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

    const getCanvasTheme = useCallback(() => {
        return isDarkModeRef.current ? CANVAS_THEMES.dark : CANVAS_THEMES.light;
    }, []);

    const calculateMaxResolution = useCallback(() => {
        const width = window.innerWidth;
        const height = window.innerHeight;
        const tier = performanceTierRef.current;

        if (tier === 'low') {
            return { w: Math.min(width, 1280), h: Math.min(height, 720) };
        } else if (tier === 'medium') {
            return { w: Math.min(width, 1920), h: Math.min(height, 1080) };
        } else {
            return { w: Math.min(width, 2560), h: Math.min(height, 1440) };
        }
    }, []);

    const initStars = useCallback(
        (w: number, h: number) => {
            const canvasTheme = getCanvasTheme();
            const screenArea = w * h;
            const tier = performanceTierRef.current;

            let baseStarCount;
            switch (tier) {
                case 'low':
                    baseStarCount = 80;
                    break;
                case 'medium':
                    baseStarCount = 120;
                    break;
                case 'high':
                    baseStarCount = 160;
                    break;
                default:
                    baseStarCount = 120;
            }

            const densityFactor = Math.min(1, screenArea / (1920 * 1080));
            const starCount = Math.floor(baseStarCount * densityFactor);

            const sizeMultiplier = Math.min(1.8, w / 1920);

            const stars: Star[] = [];
            for (let i = 0; i < starCount; i++) {
                const baseSize = Math.random() + 0.2;
                const size = baseSize * sizeMultiplier;

                stars.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    size,
                    speed:
                        canvasTheme.starSpeedMin +
                        Math.random() *
                            (canvasTheme.starSpeedMax -
                                canvasTheme.starSpeedMin),
                    brightness: Math.random() * 0.4 + 0.3,
                    pulseSpeed: Math.random() * 0.004 + 0.001,
                    twinkle: Math.random() * Math.PI * 2,
                });
            }
            return stars;
        },
        [getCanvasTheme],
    );

    const initNebulas = useCallback(
        (w: number, h: number) => {
            const canvasTheme = getCanvasTheme();
            const tier = performanceTierRef.current;

            const nebulaCount = tier === 'low' ? 1 : 2;

            return Array.from({ length: nebulaCount }).map(() => ({
                x: Math.random() * w,
                y: Math.random() * h,
                radius: Math.random() * 80 + 40,
                speedX: Math.random() * 0.03 - 0.015,
                speedY: Math.random() * 0.03 - 0.015,
                color: canvasTheme.nebulaColors[
                    Math.floor(Math.random() * canvasTheme.nebulaColors.length)
                ],
            }));
        },
        [getCanvasTheme],
    );

    const ensureShootingPool = useCallback(() => {
        if (shootingPoolRef.current.length) return;
        const tier = performanceTierRef.current;
        const poolSize = tier === 'low' ? 1 : 2;
        shootingPoolRef.current = Array.from({ length: poolSize }).map(() => ({
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
            s.vx = -speed * 0.5;
            s.vy = speed;
            s.life = 1;
            s.trail = [];
        },
        [getCanvasTheme],
    );

    const initPlanetCanvases = useCallback(() => {
        planetCanvasesRef.current = [];
        planetDrawnRef.current = [false, false];

        const canvas1 = document.createElement('canvas');
        const canvas2 = document.createElement('canvas');

        const tier = performanceTierRef.current;
        const size1 = tier === 'low' ? 90 : 120;
        const size2 = tier === 'low' ? 60 : 90;

        canvas1.width = size1;
        canvas1.height = size1;
        canvas2.width = size2;
        canvas2.height = size2;

        planetCanvasesRef.current.push(canvas1, canvas2);
    }, []);

    const drawPlanetToCanvas = useCallback(
        (planetIndex: number) => {
            if (!planetCanvasesRef.current[planetIndex]) return;

            const canvas = planetCanvasesRef.current[planetIndex];
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius =
                planetIndex === 0 ? canvas.width * 0.4 : canvas.width * 0.4;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const theme = getCanvasTheme();

            const gradient = ctx.createRadialGradient(
                centerX,
                centerY,
                0,
                centerX,
                centerY,
                radius,
            );

            theme.planetGradientStops.forEach(({ offset, color }) => {
                gradient.addColorStop(offset, color);
            });

            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
            ctx.fill();

            const ringGradient = ctx.createLinearGradient(
                centerX - radius * 1.2,
                centerY,
                centerX + radius * 1.2,
                centerY,
            );

            theme.ringGradientStops.forEach(({ offset, color }) => {
                ringGradient.addColorStop(offset, color);
            });

            ctx.strokeStyle = ringGradient;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.ellipse(
                centerX,
                centerY,
                radius * 1.2,
                radius * 0.2,
                0,
                0,
                Math.PI * 2,
            );
            ctx.stroke();

            planetDrawnRef.current[planetIndex] = true;
        },
        [getCanvasTheme],
    );

    const setCanvasSize = useCallback(
        (canvas: HTMLCanvasElement, ctx: CanvasRenderingContext2D) => {
            dprRef.current = 1;

            const maxRes = calculateMaxResolution();
            const cssW = maxRes.w;
            const cssH = maxRes.h;
            sizeRef.current = { w: cssW, h: cssH };

            canvas.width = Math.floor(cssW * dprRef.current);
            canvas.height = Math.floor(cssH * dprRef.current);
            canvas.style.width = `${cssW}px`;
            canvas.style.height = `${cssH}px`;

            ctx.setTransform(dprRef.current, 0, 0, dprRef.current, 0, 0);

            starsRef.current = initStars(cssW, cssH);
            nebulasRef.current = initNebulas(cssW, cssH);

            planetDrawnRef.current = [false, false];
        },
        [initStars, initNebulas, calculateMaxResolution],
    );

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        ensureShootingPool();
        initPlanetCanvases();

        let resizeTimeout: NodeJS.Timeout;
        const onResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                setCanvasSize(canvas, ctx);
            }, 300);
        };

        const handleVisibilityChange = () => {
            isVisibleRef.current = document.visibilityState === 'visible';
            lastFrameTimeRef.current = performance.now();
        };

        setCanvasSize(canvas, ctx);
        window.addEventListener('resize', onResize, { passive: true });
        document.addEventListener('visibilitychange', handleVisibilityChange);

        lastFrameTimeRef.current = performance.now();

        const animate = (ts: number) => {
            if (!isVisibleRef.current) {
                animationRef.current = requestAnimationFrame(animate);
                return;
            }

            const deltaTime = ts - lastFrameTimeRef.current;

            if (deltaTime < FRAME_INTERVAL) {
                animationRef.current = requestAnimationFrame(animate);
                return;
            }

            lastFrameTimeRef.current = ts - (deltaTime % FRAME_INTERVAL);
            frameCountRef.current++;

            const { w, h } = sizeRef.current;
            const canvasTheme = getCanvasTheme();

            ctx.fillStyle = canvasTheme.background;
            ctx.fillRect(0, 0, w, h);

            const nebulas = nebulasRef.current;
            for (let i = 0; i < nebulas.length; i++) {
                const n = nebulas[i];
                n.x += n.speedX * (deltaTime / 16.67);
                n.y += n.speedY * (deltaTime / 16.67);

                if (n.x > w + 150) n.x = -150;
                if (n.x < -150) n.x = w + 150;
                if (n.y > h + 150) n.y = -150;
                if (n.y < -150) n.y = h + 150;

                const gradient = ctx.createRadialGradient(
                    n.x,
                    n.y,
                    0,
                    n.x,
                    n.y,
                    n.radius,
                );
                gradient.addColorStop(0, n.color);
                gradient.addColorStop(1, 'transparent');
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(n.x, n.y, n.radius, 0, Math.PI * 2);
                ctx.fill();
            }

            const stars = starsRef.current;
            const starColor = canvasTheme.starColor;
            const isHighRes = w >= 1920;
            const tier = performanceTierRef.current;

            for (let i = 0; i < stars.length; i++) {
                const s = stars[i];
                s.y += s.speed * (deltaTime / 16.67);
                if (s.y > h) {
                    s.y = 0;
                    s.x = Math.random() * w;
                }

                if (tier !== 'low' && frameCountRef.current % 5 === 0) {
                    s.twinkle += s.pulseSpeed * (deltaTime / 16.67);
                    s.brightness = 0.5 + Math.sin(s.twinkle) * 0.2;
                }

                let starAlpha = s.brightness * canvasTheme.starAlphaMultiplier;

                if (tier === 'high' && s.size > 1.2 && isHighRes) {
                    starAlpha *= 1.5;
                }

                if (s.size > 0.5) {
                    ctx.fillStyle = `rgba(${starColor}, ${starColor}, ${starColor}, ${starAlpha})`;
                    ctx.beginPath();
                    ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
                    ctx.fill();

                    if (
                        tier === 'high' &&
                        s.size > 1.2 &&
                        isHighRes &&
                        frameCountRef.current % 3 === 0
                    ) {
                        ctx.fillStyle = `rgba(${starColor}, ${starColor}, ${starColor}, ${starAlpha * 0.3})`;
                        ctx.beginPath();
                        ctx.arc(s.x, s.y, s.size * 1.2, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }
            }

            if (w > 768) {
                if (!planetDrawnRef.current[0]) {
                    drawPlanetToCanvas(0);
                }

                if (planetDrawnRef.current[0] && planetCanvasesRef.current[0]) {
                    planetRotationsRef.current[0] +=
                        0.002 * (deltaTime / 16.67);

                    ctx.save();
                    ctx.translate(w * 0.8, h * 0.2);
                    ctx.rotate(planetRotationsRef.current[0]);
                    ctx.drawImage(
                        planetCanvasesRef.current[0],
                        -planetCanvasesRef.current[0].width / 2,
                        -planetCanvasesRef.current[0].height / 2,
                    );
                    ctx.restore();
                }

                if (!planetDrawnRef.current[1]) {
                    drawPlanetToCanvas(1);
                }

                if (planetDrawnRef.current[1] && planetCanvasesRef.current[1]) {
                    planetRotationsRef.current[1] +=
                        0.003 * (deltaTime / 16.67);

                    ctx.save();
                    ctx.translate(w * 0.2, h * 0.7);
                    ctx.rotate(planetRotationsRef.current[1]);
                    ctx.drawImage(
                        planetCanvasesRef.current[1],
                        -planetCanvasesRef.current[1].width / 2,
                        -planetCanvasesRef.current[1].height / 2,
                    );
                    ctx.restore();
                }
            }

            if (tier !== 'low' && timeRef.current - lastSpawnRef.current > 5) {
                lastSpawnRef.current = timeRef.current;
                if (Math.random() < 0.005) spawnShootingStar(w);
            }

            const pool = shootingPoolRef.current;
            for (let i = 0; i < pool.length; i++) {
                const s = pool[i];
                if (!s.active) continue;

                s.life -= 0.002 * (deltaTime / 16.67);
                s.x += s.vx * (deltaTime / 16.67);
                s.y += s.vy * (deltaTime / 16.67);

                if (tier !== 'low' && frameCountRef.current % 2 === 0) {
                    s.trail.push({ x: s.x, y: s.y, life: s.life });
                }
                if (s.trail.length > 3) s.trail.shift();

                if (s.life <= 0 || s.y > h + 50) {
                    s.active = false;
                    continue;
                }

                if (tier !== 'low') {
                    for (let j = 0; j < s.trail.length; j++) {
                        if (j > 0) {
                            const point = s.trail[j];
                            const prev = s.trail[j - 1];
                            const alpha =
                                point.life * (j / s.trail.length) * 0.4;

                            ctx.beginPath();
                            ctx.moveTo(prev.x, prev.y);
                            ctx.lineTo(point.x, point.y);

                            ctx.strokeStyle = `rgba(${canvasTheme.trailColor}, ${canvasTheme.trailColor}, ${canvasTheme.trailColor}, ${alpha})`;
                            ctx.lineWidth = 1;
                            ctx.stroke();
                        }
                    }
                }

                ctx.fillStyle = canvasTheme.shootingStarColor;
                ctx.beginPath();
                ctx.arc(s.x, s.y, 2, 0, Math.PI * 2);
                ctx.fill();
            }

            timeRef.current += 0.005 * (deltaTime / 16.67);
            animationRef.current = requestAnimationFrame(animate);
        };

        animationRef.current = requestAnimationFrame(animate);

        return () => {
            window.removeEventListener('resize', onResize);
            document.removeEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
            clearTimeout(resizeTimeout);
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current);
            }
        };
    }, [
        ensureShootingPool,
        setCanvasSize,
        spawnShootingStar,
        getCanvasTheme,
        initPlanetCanvases,
        drawPlanetToCanvas,
        FRAME_INTERVAL,
    ]);

    return (
        <>
            <canvas
                ref={canvasRef}
                className="pointer-events-none fixed inset-0"
                style={{ zIndex: 0 }}
            />
        </>
    );
}
