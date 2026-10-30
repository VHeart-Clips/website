<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy"
          content="default-src 'none'; style-src 'unsafe-inline'; img-src data:; form-action 'none'; base-uri 'none'">
    <title>VHeart User Data export - {{ $user->name }} ({{ $user->id }})</title>
    <style>
        :root {
            color-scheme: light dark;
            --primary: #e71d73;
            --line: color-mix(in srgb, currentColor 25%, transparent);
            --tint: color-mix(in srgb, currentColor 8%, transparent);
        }

        body {
            font: 1rem/1.6 system-ui, sans-serif;
            max-width: 50rem;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        h1, h2, h3, h4 {
            line-height: 1.25;
            margin: 2rem 0 .75rem;
        }

        h4 {
            font-size: 1rem;
        }

        :target {
            background: color-mix(in srgb, var(--primary) 12%, transparent);
        }

        ul ul {
            margin-block: .25rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: .9375rem;
        }

        th, td {
            border: 1px solid var(--line);
            padding: .4rem .6rem;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        thead th {
            background: var(--tint);
        }

        code {
            font-family: ui-monospace, monospace;
            background: var(--tint);
            padding: .1rem .3rem;
            border-radius: .25rem;
        }

        h4 code, pre code {
            background: none;
            padding: 0;
        }

        pre {
            overflow-x: auto;
            padding: .75rem 1rem;
            background: var(--tint);
            border-radius: .25rem;
        }

        blockquote {
            margin: 1rem 0;
            padding: .75rem 1rem;
            border-left: .25rem solid var(--primary);
            background: color-mix(in srgb, var(--primary) 10%, transparent);
        }

        blockquote p {
            margin: 0;
        }

        img {
            display: block;
            max-width: 100%;
            height: auto;
        }

        hr {
            border: 0;
            border-top: 1px solid var(--line);
            margin: 2rem 0;
        }

        @media print {
            :root {
                print-color-adjust: exact;
            }

            @page {
                margin: 1.5cm;
            }

            body {
                max-width: none;
                margin: 0;
            }

            h2:not(:first-of-type) {
                break-before: page;
            }

            h2, h3, h4 {
                break-after: avoid;
            }

            tr, pre, blockquote {
                break-inside: avoid;
            }

            hr {
                display: none;
            }
        }
    </style>
</head>
<body>
{!! $content !!}
</body>
</html>
