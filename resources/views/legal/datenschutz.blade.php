<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Datenschutz – VHeart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.20) 0%, rgba(10,10,26,0) 45%),
                radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.14) 0%, rgba(10,10,26,0) 50%),
                #0a0a1a;
            color: #ffffff;
        }

        .legal-page {
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
            padding: 4.5rem 1.5rem 2rem;
            position: relative;
            z-index: 10;
        }

        .legal-back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(145, 70, 255, 0.35);
            background: linear-gradient(90deg, rgba(145, 70, 255, 0.22), rgba(0, 174, 255, 0.14));
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .legal-back-btn:hover {
            background: linear-gradient(90deg, rgba(145, 70, 255, 0.3), rgba(0, 174, 255, 0.2));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .legal-header,
        .legal-card,
        .site-footer {
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(18px);
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
            margin-bottom: 24px;
        }

        .legal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .legal-header h1 {
            margin: 0 0 10px;
            font-size: 22px;
            letter-spacing: -0.01em;
            font-weight: 700;
        }

        .legal-intro {
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.5;
            margin: 0;
            text-align: left;
            font-size: 0.98rem;
        }

        .legal-card h2 {
            margin: 0 0 16px;
            font-size: 1.25rem;
            font-weight: 600;
            color: #fff;
        }

        .legal-card h3 {
            margin: 0 0 12px;
            font-size: 1.05rem;
            font-weight: 600;
            color: #fff;
            opacity: 0.95;
        }

        .legal-card p {
            margin: 0 0 16px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
        }

        .legal-card p:last-child {
            margin-bottom: 0;
        }

        .legal-card p strong {
            color: #fff;
            font-weight: 600;
        }

        .legal-page a {
            color: #fff;
            opacity: 0.9;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: opacity 0.2s;
        }

        .legal-page a:hover {
            opacity: 1;
        }

        .external-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #9146FF;
            text-decoration: none;
            font-weight: 500;
            background: linear-gradient(90deg, rgba(145, 70, 255, 0.15), rgba(0, 174, 255, 0.08));
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid rgba(145, 70, 255, 0.2);
            margin-top: 8px;
        }

        .external-link:hover {
            background: linear-gradient(90deg, rgba(145, 70, 255, 0.25), rgba(0, 174, 255, 0.15));
            color: #a566ff;
        }

        .external-link::after {
            content: "↗";
            font-size: 0.85em;
            opacity: 0.8;
        }

        .site-footer {
            margin-top: 3rem;
            margin-bottom: 0;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .footer-copy {
            margin: 0;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.9rem;
        }

        .footer-nav {
            display: flex;
            gap: 1rem;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #fff;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .legal-page {
                padding: 5.5rem 1.25rem 1.5rem;
            }

            .legal-back-btn {
                top: 15px;
                left: 15px;
                font-size: 0.9rem;
                padding: 8px 12px;
            }

            .legal-header,
            .legal-card,
            .site-footer {
                padding: 20px;
                margin-bottom: 20px;
            }

            .legal-header h1 {
                font-size: 20px;
            }

            .legal-intro {
                font-size: 0.95rem;
            }

            .legal-card h2 {
                font-size: 1.15rem;
            }

            .legal-card h3 {
                font-size: 1rem;
            }

            .legal-card p {
                font-size: 0.93rem;
            }

            .external-link {
                padding: 3px 8px;
                font-size: 0.9rem;
            }

            .footer-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .footer-nav {
                width: 100%;
                justify-content: flex-start;
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .legal-page {
                padding: 6rem 1rem 1rem;
            }

            .legal-back-btn {
                top: 12px;
                left: 12px;
                font-size: 0.85rem;
                padding: 7px 10px;
            }

            .legal-header,
            .legal-card,
            .site-footer {
                padding: 18px;
                border-radius: 16px;
            }

            .legal-header h1 {
                font-size: 18px;
            }

            .legal-intro {
                font-size: 0.93rem;
            }

            .legal-card h2 {
                font-size: 1.1rem;
            }

            .legal-card h3 {
                font-size: 0.98rem;
            }

            .legal-card p {
                font-size: 0.91rem;
            }
        }

        @supports not (backdrop-filter: blur(18px)) {
            .legal-header,
            .legal-card,
            .site-footer {
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: none;
            }

            .legal-back-btn {
                backdrop-filter: none;
                background: linear-gradient(90deg, rgba(145, 70, 255, 0.35), rgba(0, 174, 255, 0.25));
            }
        }
    </style>
</head>
<body>
<main class="legal-page">
    <a href="/" class="legal-back-btn" aria-label="Zurück zur Startseite">← Zurück</a>

    <header class="legal-header">
        <h1>Datenschutzerklärung</h1>
        <p class="legal-intro">
            Diese Website dient ausschließlich der Darstellung von Inhalten. Wir setzen keine Cookies ein,
            nutzen kein Tracking und verarbeiten keine personenbezogenen Daten zu Analyse- oder Marketingzwecken.
            Bei Nutzung externer Links/Anbieter kann es zu Datenverarbeitungen durch Dritte kommen.
        </p>
    </header>

    <section class="legal-card">
        <h2>Verantwortlicher</h2>
        <p>
            <strong>VHeart</strong><br>
            Jennifer Matern<br>
            c/o PURE4U GbR<br>
            Amtstraße 22<br>
            44575 Castrop-Rauxel<br>
            Deutschland
        </p>
        <p>
            <strong>E-Mail:</strong>
            <a href="mailto:meynhero@gmail.com">meynhero@gmail.com</a>
        </p>
        <p>
            <strong>Telefon:</strong>
            <a href="tel:-------">-------</a>
        </p>
    </section>

    <section class="legal-card">
        <h2>Technische Bereitstellung</h2>
        <p>
            Beim Aufruf der Website können technisch notwendige Daten (z. B. IP-Adresse, Zeitpunkt des Zugriffs)
            durch den Hosting- oder Infrastruktur-Anbieter verarbeitet werden. Diese Verarbeitung erfolgt außerhalb
            unseres direkten Einflussbereichs.
        </p>
    </section>

    <section class="legal-card">
        <h2>Drittanbieter</h2>
        <p>
            Diese Website enthält externe Links bzw. lädt Inhalte von Drittanbietern. Beim Aufruf dieser Angebote
            können personenbezogene Daten (z. B. IP-Adresse) durch den jeweiligen Anbieter verarbeitet werden.
        </p>
    </section>

    <section class="legal-card">
        <h3>Google Fonts</h3>
        <p>
            Zur Darstellung der Schriftart wird Google Fonts verwendet. Dabei kann eine Verbindung zu Servern von
            Google hergestellt werden. Verantwortlich ist Google Ireland Limited.
        </p>
        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="external-link">
            Datenschutzerklärung von Google
        </a>
    </section>

    <section class="legal-card">
        <h3>Discord</h3>
        <p>
            Diese Website enthält einen externen Link zu Discord. Nach dem Anklicken liegt die Datenverarbeitung
            ausschließlich in der Verantwortung von Discord.
        </p>
        <a href="https://discord.com" target="_blank" rel="noopener noreferrer" class="external-link">
            Discord Website
        </a>
        <br>
        <a href="https://discord.com/privacy" target="_blank" rel="noopener noreferrer" class="external-link">
            Discord Datenschutzerklärung
        </a>
    </section>

    <section class="legal-card">
        <h3>YouTube</h3>
        <p>
            Diese Website enthält einen externen Link zu YouTube. Mit dem Aufruf des Links verlassen Sie unsere
            Website. Verantwortlich ist Google Ireland Limited.
        </p>
        <a href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" class="external-link">
            YouTube Website
        </a>
        <br>
        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="external-link">
            YouTube/Google Datenschutzerklärung
        </a>
    </section>

    <section class="legal-card">
        <h3>CATAAS</h3>
        <p>
            Beim Abruf von Bildern über cataas.com wird eine Verbindung zu Servern dieses Anbieters hergestellt.
            Verantwortlich ist ausschließlich der Betreiber von CATAAS.
        </p>
        <a href="https://cataas.com" target="_blank" rel="noopener noreferrer" class="external-link">
            CATAAS Website
        </a>
    </section>

    <section class="legal-card">
        <h2>Ihre Rechte</h2>
        <p>
            Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung sowie auf
            Datenübertragbarkeit Ihrer personenbezogenen Daten im Rahmen der DSGVO.
        </p>
    </section>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-copy">© 2026 VHeart. Alle Rechte vorbehalten.</p>
            <nav class="footer-nav" aria-label="Footer Navigation">
                <a class="footer-link" href="/privacy">Datenschutz</a>
                <a class="footer-link" href="/imprint">Impressum</a>
            </nav>
        </div>
    </footer>
</main>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
</body>
</html>
