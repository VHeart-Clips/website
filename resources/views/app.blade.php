<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>
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
    @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
    @cookieconsentscripts
</head>
<body class="font-inter antialiased">

<noscript>
    <div style="
         position: fixed;
         inset: 0;
         z-index: 9999;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 24px;
         background:
         radial-gradient(circle at 20% 30%, rgba(145, 70, 255, 0.20) 0%, rgba(10,10,26,0) 45%),
         radial-gradient(circle at 80% 70%, rgba(0, 174, 255, 0.14) 0%, rgba(10,10,26,0) 50%),
         #0a0a1a;
         color: #ffffff;
         font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
         ">
        <div style="
            width: 100%;
            max-width: 720px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(0,0,0,0.35);
            backdrop-filter: blur(18px);
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
            text-align: center;
            ">
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <img src="{{ Vite::asset("resources/images/svg/logo-full-dark.svg") }}" style="height: auto; width: 50%">
            </div>
            <div style="display:flex; flex-direction: column; align-items:center; gap:14px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="text-align: center;">
                        <h1 style="margin:0; font-size: 22px; letter-spacing: -0.01em;">
                            ⚠️ JavaScript ist deaktiviert ⚠️
                        </h1>
                        <p style="margin:10px 0 0; line-height: 1.5; color: rgba(255,255,255,0.85); max-width: 500px;">
                            Diese Website benötigt JavaScript, um korrekt zu funktionieren. Du kannst die rechtlichen
                            Seiten trotzdem öffnen:
                        </p>
                    </div>
                </div>
                <div style="margin-top: 10px; display:flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                    <a href="/imprint" style="
                     display:inline-flex; align-items:center; gap:10px;
                     padding: 10px 14px;
                     border-radius: 999px;
                     border: 1px solid rgba(145,70,255,0.35);
                     background: linear-gradient(90deg, rgba(145,70,255,0.22), rgba(0,174,255,0.14));
                     color:#fff; text-decoration:none; font-weight: 600;
                     ">Impressum</a>
                    <a href="/privacy" style="
                     display:inline-flex; align-items:center; gap:10px;
                     padding: 10px 14px;
                     border-radius: 999px;
                     border: 1px solid rgba(145,70,255,0.35);
                     background: linear-gradient(90deg, rgba(145,70,255,0.22), rgba(0,174,255,0.14));
                     color:#fff; text-decoration:none; font-weight: 600;
                     ">Datenschutz</a>
                </div>
            </div>
        </div>
    </div>
</noscript>
@inertia
@cookieconsentview
</body>
</html>
