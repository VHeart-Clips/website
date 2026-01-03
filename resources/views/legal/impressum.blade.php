<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @viteReactRefresh
    @cookieconsentscripts
</head>
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
    }

    body {
        background: radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.20) 0%, rgba(10, 10, 26, 0) 45%),
        radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.14) 0%, rgba(10, 10, 26, 0) 50%),
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
    }

    .legal-card h2 {
        margin: 0 0 16px;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .legal-card h3 {
        margin: 0 0 12px;
        font-size: 1.05rem;
        font-weight: 600;
    }

    .legal-card p {
        margin: 0 0 16px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.85);
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

        .legal-card h2 {
            font-size: 1.15rem;
        }

        .legal-card p {
            font-size: 0.95rem;
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
        }

        .legal-header h1 {
            font-size: 18px;
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
<body>
<main class="legal-page">
    <a href="/" class="legal-back-btn" aria-label="Zurück zur Startseite">← Zurück</a>

    <header class="legal-header">
        <h1>Impressum</h1>
        <p class="legal-intro">
            Angaben gemäß § 5 DDG
        </p>
    </header>

    <section class="legal-card">
        <h2>Anbieter</h2>
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
        <h2>Haftung für Inhalte</h2>
        <p>
            Als Diensteanbieter sind wir gemäß § 7 Abs. 1 DDG für eigene Inhalte auf diesen Seiten nach den allgemeinen
            Gesetzen verantwortlich. Nach §§ 8 bis 10 DDG sind wir als Diensteanbieter jedoch nicht verpflichtet,
            übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf
            eine rechtswidrige Tätigkeit hinweisen.
        </p>
        <p>
            Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen
            bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer
            konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese
            Inhalte umgehend entfernen.
        </p>
    </section>

    <section class="legal-card">
        <h2>Haftung für Links</h2>
        <p>
            Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben.
            Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten
            Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich.
        </p>
        <p>
            Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft.
            Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche
            Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht
            zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.
        </p>
    </section>

    <section class="legal-card">
        <h2>Urheberrecht</h2>
        <p>
            Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen
            Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der
            Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.
        </p>
        <p>
            Soweit Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter
            beachtet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen
            entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend
            entfernen.
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
