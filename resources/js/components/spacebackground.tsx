import { useLayoutEffect, useRef } from 'react';

const StaticSpaceBackground = () => {
    const containerRef = useRef<HTMLDivElement>(null);
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-expect-error
    const resizeTimeoutRef = useRef<number>();

    useLayoutEffect(() => {
        const drawDark = (
            container: HTMLDivElement,
            width: number,
            height: number,
        ) => {
            container.innerHTML = '';
            const isMobile = width <= 768;

            const backgroundDiv = document.createElement('div');
            backgroundDiv.style.cssText = `position: absolute; inset: 0; background: #0a0a1a; z-index: 0;`;
            container.appendChild(backgroundDiv);

            const starColor = 'rgba(255,255,255,';
            const starAlphaMultiplier = 1;
            const staticStars = [
                { x: 10, y: 10, s: 3.0, b: 0.9 },
                { x: 15, y: 25, s: 2.5, b: 0.8 },
                { x: 5, y: 30, s: 4.0, b: 1.0 },
                { x: 20, y: 15, s: 2.0, b: 0.7 },
                { x: 8, y: 40, s: 3.5, b: 0.85 },

                { x: 85, y: 15, s: 2.8, b: 0.8 },
                { x: 90, y: 35, s: 2.2, b: 0.6 },
                { x: 80, y: 25, s: 3.2, b: 0.9 },
                { x: 95, y: 10, s: 2.7, b: 0.75 },
                { x: 75, y: 45, s: 4.5, b: 1.0 },

                { x: 25, y: 65, s: 2.0, b: 0.7 },
                { x: 35, y: 75, s: 3.0, b: 0.8 },
                { x: 45, y: 60, s: 2.5, b: 0.65 },
                { x: 30, y: 85, s: 3.2, b: 0.9 },
                { x: 40, y: 95, s: 4.0, b: 1.0 },

                { x: 65, y: 20, s: 2.3, b: 0.7 },
                { x: 70, y: 50, s: 3.1, b: 0.85 },
                { x: 55, y: 35, s: 2.8, b: 0.75 },
                { x: 60, y: 80, s: 3.5, b: 0.9 },
                { x: 50, y: 70, s: 2.2, b: 0.6 },

                { x: 85, y: 70, s: 2.6, b: 0.8 },
                { x: 90, y: 85, s: 3.8, b: 0.95 },
                { x: 75, y: 80, s: 2.9, b: 0.7 },
                { x: 80, y: 95, s: 3.3, b: 0.85 },
                { x: 95, y: 75, s: 2.4, b: 0.65 },

                { x: 50, y: 50, s: 4.0, b: 1.0 },
                { x: 40, y: 40, s: 2.7, b: 0.75 },
                { x: 60, y: 60, s: 3.2, b: 0.85 },
                { x: 45, y: 55, s: 3.5, b: 0.9 },
                { x: 55, y: 45, s: 2.0, b: 0.6 },

                { x: 12, y: 60, s: 2.8, b: 0.8 },
                { x: 22, y: 35, s: 3.0, b: 0.85 },
                { x: 32, y: 90, s: 2.5, b: 0.7 },
                { x: 42, y: 20, s: 3.2, b: 0.9 },
                { x: 52, y: 85, s: 2.9, b: 0.8 },

                { x: 62, y: 40, s: 3.5, b: 0.95 },
                { x: 72, y: 65, s: 2.6, b: 0.75 },
                { x: 82, y: 30, s: 3.0, b: 0.85 },
                { x: 92, y: 55, s: 2.8, b: 0.8 },
                { x: 98, y: 25, s: 3.7, b: 1.0 },
            ];

            staticStars.forEach((star) => {
                const starDiv = document.createElement('div');
                const baseSize = isMobile ? star.s * 0.9 : star.s;
                const size = baseSize * (isMobile ? 1.1 : 1.0);
                const opacity = star.b * starAlphaMultiplier;
                starDiv.style.cssText = `
                    position: absolute; left: ${star.x}%; top: ${star.y}%;
                    width: ${size}px; height: ${size}px;
                    background: ${starColor}${opacity}); border-radius: 50%;
                    transform: translate(-50%, -50%); z-index: 2;
                    ${isMobile ? 'filter: brightness(1.2);' : ''}
                `;
                container.appendChild(starDiv);
            });

            const planetCanvas = document.createElement('canvas');
            planetCanvas.width = width;
            planetCanvas.height = height;
            planetCanvas.style.cssText = `position: absolute; inset: 0; z-index: 3; pointer-events: none;`;
            container.appendChild(planetCanvas);

            const ctx = planetCanvas.getContext('2d');
            if (!ctx) return;

            const planetColor1 = 'rgba(100, 65, 165, ';
            const planetColor2 = 'rgba(145, 70, 255, ';

            const p1X = isMobile ? width * 0.85 : width * 0.8;
            const p1Y = isMobile ? height * 0.15 : height * 0.2;
            const p1R = isMobile ? 32 : 48;
            const g1 = ctx.createRadialGradient(p1X, p1Y, 0, p1X, p1Y, p1R);
            g1.addColorStop(0, planetColor1 + '0.8)');
            g1.addColorStop(0.6, planetColor1 + '0.6)');
            g1.addColorStop(1, planetColor1 + '0.4)');
            ctx.fillStyle = g1;
            ctx.beginPath();
            ctx.arc(p1X, p1Y, p1R, 0, Math.PI * 2);
            ctx.fill();

            const r1G = ctx.createLinearGradient(
                p1X - p1R * 1.2,
                p1Y,
                p1X + p1R * 1.2,
                p1Y,
            );
            r1G.addColorStop(0, planetColor2 + '0)');
            r1G.addColorStop(0.3, planetColor2 + '0.15)');
            r1G.addColorStop(0.7, planetColor2 + '0.15)');
            r1G.addColorStop(1, planetColor2 + '0)');
            ctx.strokeStyle = r1G;
            ctx.lineWidth = isMobile ? 1.5 : 2;
            ctx.beginPath();
            ctx.ellipse(p1X, p1Y, p1R * 1.2, p1R * 0.2, 0.5, 0, Math.PI * 2);
            ctx.stroke();

            const p2X = isMobile ? width * 0.15 : width * 0.2;
            const p2Y = isMobile ? height * 0.85 : height * 0.7;
            const p2R = isMobile ? 24 : 36;
            const g2 = ctx.createRadialGradient(p2X, p2Y, 0, p2X, p2Y, p2R);
            g2.addColorStop(0, planetColor1 + '0.8)');
            g2.addColorStop(0.6, planetColor1 + '0.6)');
            g2.addColorStop(1, planetColor1 + '0.4)');
            ctx.fillStyle = g2;
            ctx.beginPath();
            ctx.arc(p2X, p2Y, p2R, 0, Math.PI * 2);
            ctx.fill();

            const r2G = ctx.createLinearGradient(
                p2X - p2R * 1.2,
                p2Y,
                p2X + p2R * 1.2,
                p2Y,
            );
            r2G.addColorStop(0, planetColor2 + '0)');
            r2G.addColorStop(0.3, planetColor2 + '0.15)');
            r2G.addColorStop(0.7, planetColor2 + '0.15)');
            r2G.addColorStop(1, planetColor2 + '0)');
            ctx.strokeStyle = r2G;
            ctx.lineWidth = isMobile ? 1.5 : 2;
            ctx.beginPath();
            ctx.ellipse(p2X, p2Y, p2R * 1.2, p2R * 0.2, 1.2, 0, Math.PI * 2);
            ctx.stroke();
        };

        const drawLight = (container: HTMLDivElement, width: number) => {
            container.innerHTML = '';
            const isMobile = width <= 768;

            const sky = document.createElement('div');
            sky.style.cssText = `
                position: absolute; inset: 0;
                background: linear-gradient(180deg, #bae6fd 0%, #e0f2fe 60%, #ffffff 100%);
                z-index: 0;
            `;
            container.appendChild(sky);

            const sun = document.createElement('div');
            const sunSize = isMobile ? 80 : 120;
            sun.style.cssText = `
                position: absolute; top: 8%; right: 10%;
                width: ${sunSize}px; height: ${sunSize}px;
                background: radial-gradient(circle, rgba(255, 255, 250, 0.8) 0%, rgba(254, 240, 138, 0.5) 50%, rgba(254, 240, 138, 0) 90%);
                border-radius: 50%;
                z-index: 1;
                box-shadow: 0 0 80px 30px rgba(254, 240, 138, 0.25);
                filter: blur(8px);
            `;
            container.appendChild(sun);

            const cloudPos = [
                { t: 15, l: 20, s: 1.6 },
                { t: 10, l: 65, s: 2.2 },
                { t: 22, l: 85, s: 1.3 },
            ];
            cloudPos.forEach((p) => {
                const cloud = document.createElement('div');
                const baseSize = isMobile ? 50 : 80;
                const width = baseSize * p.s;
                const height = width * 0.6;
                cloud.style.cssText = `
                    position: absolute; top: ${p.t}%; left: ${p.l}%;
                    width: ${width}px; height: ${height}px;
                    background: rgba(255, 255, 255, 0.85);
                    border-radius: 50%;
                    z-index: 1;
                    transform: translate(-50%, -50%);
                    filter: blur(18px);
                `;
                container.appendChild(cloud);
            });

            const mtns = [
                {
                    x: -10,
                    w: 75,
                    h: 45,
                    l: '#cbd5e1',
                    s: '#94a3b8',
                    p: 'polygon(0% 100%, 30% 20%, 70% 100%)',
                    ps: 'polygon(0% 100%, 30% 20%, 30% 100%)',
                    sn: 'polygon(25% 33%, 30% 20%, 35% 33%)',
                    z: 2,
                },
                {
                    x: 15,
                    w: 95,
                    h: 68,
                    l: '#94a3b8',
                    s: '#64748b',
                    p: 'polygon(0% 100%, 45% 0%, 90% 100%)',
                    ps: 'polygon(0% 100%, 45% 0%, 45% 100%)',
                    sn: 'polygon(40% 12%, 45% 0%, 50% 12%)',
                    z: 3,
                },
                {
                    x: 50,
                    w: 70,
                    h: 55,
                    l: '#64748b',
                    s: '#475569',
                    p: 'polygon(0% 100%, 40% 15%, 85% 100%)',
                    ps: 'polygon(0% 100%, 40% 15%, 40% 100%)',
                    sn: 'polygon(35% 25%, 40% 15%, 45% 25%)',
                    z: 4,
                },
            ];

            mtns.forEach((m) => {
                const mtnGroup = document.createElement('div');
                mtnGroup.style.cssText = `position: absolute; bottom: 18%; left: ${m.x}%; width: ${m.w}%; height: ${m.h}%; z-index: ${m.z};`;

                const body = document.createElement('div');
                body.style.cssText = `position: absolute; inset: 0; background: ${m.l}; clip-path: ${m.p};`;
                mtnGroup.appendChild(body);

                const shad = document.createElement('div');
                shad.style.cssText = `position: absolute; inset: 0; background: ${m.s}; clip-path: ${m.ps}; opacity: 0.95;`;
                mtnGroup.appendChild(shad);

                if (m.sn) {
                    const snow = document.createElement('div');
                    snow.style.cssText = `position: absolute; inset: 0; background: #ffffff; clip-path: ${m.sn};`;
                    mtnGroup.appendChild(snow);
                }
                container.appendChild(mtnGroup);
            });

            const lake = document.createElement('div');
            lake.style.cssText = `position: absolute; bottom: 8%; left: 0; width: 100%; height: 12.3%; background: #0284c7; z-index: 5; clip-path: polygon(0% 20%, 25% 0%, 50% 15%, 75% 5%, 100% 25%, 100% 100%, 0% 100%);`;
            container.appendChild(lake);

            const sand = document.createElement('div');
            sand.style.cssText = `position: absolute; bottom: 0; width: 100%; height: 18%; background: #fde68a; z-index: 4; clip-path: polygon(0% 35%, 15% 20%, 35% 35%, 50% 15%, 65% 35%, 85% 20%, 100% 35%, 100% 100%, 0% 100%);`;
            container.appendChild(sand);

            const groundLeft = document.createElement('div');
            groundLeft.style.cssText = `position: absolute; bottom: 0; left: 0; width: 40%; height: 20%; background: #166534; z-index: 6; clip-path: polygon(0% 0%, 100% 100%, 0% 100%);`;
            container.appendChild(groundLeft);

            const groundRight = document.createElement('div');
            groundRight.style.cssText = `position: absolute; bottom: 0; right: 0; width: 45%; height: 22%; background: #15803d; z-index: 6; clip-path: polygon(100% 0%, 100% 100%, 0% 100%);`;
            container.appendChild(groundRight);

            [12, 25, 72, 88].forEach((x, i) => {
                const tree = document.createElement('div');
                const h = (isMobile ? 40 : 80) + (i % 2) * 20;
                tree.style.cssText = `position: absolute; bottom: 6%; left: ${x}%; width: ${h * 0.4}px; height: ${h}px; background: #064e3b; clip-path: polygon(50% 0, 0 100%, 100% 100%); z-index: 8;`;
                container.appendChild(tree);
            });
        };

        const draw = () => {
            const container = containerRef.current;
            if (!container) return;
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark)
                drawDark(container, window.innerWidth, window.innerHeight);
            else drawLight(container, window.innerWidth);
        };

        draw();
        const handleResize = () => {
            if (resizeTimeoutRef.current)
                clearTimeout(resizeTimeoutRef.current);
            resizeTimeoutRef.current = window.setTimeout(draw, 150);
        };
        window.addEventListener('resize', handleResize);
        const mo = new MutationObserver(draw);
        mo.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        return () => {
            window.removeEventListener('resize', handleResize);
            mo.disconnect();
            if (resizeTimeoutRef.current)
                clearTimeout(resizeTimeoutRef.current);
        };
    }, []);

    return (
        <div
            ref={containerRef}
            className="pointer-events-none fixed inset-0"
            style={{ zIndex: 0 }}
        />
    );
};

export default StaticSpaceBackground;
