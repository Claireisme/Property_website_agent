@props(['title' => null])

@php
    $currentLocale = app()->getLocale();
@endphp

<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        :root {
            --ink: #121826;
            --muted: #606a78;
            --line: #d8e0e7;
            --surface: #f5f7fa;
            --brand: #0b6b88;
            --brand-dark: #0e4054;
            --accent: #b7791f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--ink);
            background: #ffffff;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar {
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.95);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav {
            min-height: 66px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            font-weight: 850;
        }

        .links {
            display: flex;
            align-items: center;
            gap: 18px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .disclaimer {
            background: #fff7ed;
            border-bottom: 1px solid #fed7aa;
            color: #7c2d12;
            font-size: 14px;
            padding: 10px 0;
        }

        .disclaimer strong {
            color: #431407;
        }

        .language-select {
            border: 1px solid var(--line);
            border-radius: 8px;
            width: auto;
            min-height: 36px;
            padding: 6px 34px 6px 10px;
            color: var(--ink);
            background: #ffffff;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
        }

        .hero {
            color: #ffffff;
            background:
                linear-gradient(115deg, rgba(18, 24, 38, 0.88), rgba(14, 64, 84, 0.7)),
                linear-gradient(135deg, #0e4054, #244152);
            padding: 70px 0 54px;
        }

        h1 {
            margin: 0;
            font-size: clamp(38px, 5.5vw, 64px);
            line-height: 1.04;
            letter-spacing: 0;
        }

        h2 {
            margin: 0 0 18px;
            font-size: 28px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .lead {
            max-width: 680px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 18px;
        }

        .band {
            padding: 46px 0;
        }

        .search {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 220px 120px;
            gap: 12px;
            margin-top: 24px;
        }

        input,
        select,
        textarea,
        button {
            border: 1px solid var(--line);
            border-radius: 8px;
            min-height: 44px;
            padding: 12px 13px;
            font: inherit;
        }

        button,
        .button {
            border: 0;
            background: var(--brand);
            color: #ffffff;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            min-height: 44px;
            border-radius: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
        }

        .photo {
            position: relative;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(11, 107, 136, 0.82), rgba(18, 24, 38, 0.72)),
                #dbe5ec;
            color: #ffffff;
            display: flex;
            align-items: end;
            padding: 16px;
            font-weight: 850;
        }

        .status-corner {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 176px;
            height: 176px;
            overflow: hidden;
            color: #ffffff;
            pointer-events: none;
            display: block;
            z-index: 2;
        }

        .status-corner span {
            position: absolute;
            right: -74px;
            bottom: 30px;
            width: 250px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-45deg);
            transform-origin: center;
            text-align: center;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
            box-shadow: 0 12px 24px rgba(18, 24, 38, .24);
        }

        .status-corner.available span {
            background: rgba(11, 107, 136, .9);
        }

        .status-corner.under_offer span {
            background: rgba(183, 121, 31, .9);
        }

        .status-corner.sale_agreed span {
            background: rgba(109, 40, 217, .9);
        }

        .status-corner.sold span,
        .status-corner.withdrawn span,
        .status-corner.archived span {
            background: rgba(107, 114, 128, .88);
            color: #e5e7eb;
        }

        .status-corner.draft span {
            background: rgba(156, 163, 175, .85);
            color: #f3f4f6;
        }

        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .card-body {
            padding: 18px;
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: var(--muted);
            font-size: 14px;
            margin: 10px 0 14px;
        }

        .price {
            color: var(--brand-dark);
            font-weight: 850;
            font-size: 20px;
        }

        .badge {
            display: inline-flex;
            border-radius: 999px;
            padding: 4px 10px;
            background: #edf8fb;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .muted {
            color: var(--muted);
        }

        .footer {
            border-top: 1px solid var(--line);
            color: var(--muted);
            padding: 28px 0;
            font-size: 14px;
        }

        @media (max-width: 860px) {
            .nav,
            .links {
                align-items: flex-start;
                flex-direction: column;
            }

            .grid,
            .search {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @if ($currentLocale !== \App\Support\Locales::default())
        <div class="disclaimer">
            <div class="shell">
                <strong>{{ __('site.translation_notice_title') }}:</strong>
                {{ __('site.translation_disclaimer') }}
            </div>
        </div>
    @endif

    <header class="topbar">
        <nav class="shell nav">
            <a class="brand" href="{{ \App\Support\LocaleUrl::route('home') }}">{{ config('app.name') }}</a>
            <div class="links">
                <a href="{{ \App\Support\LocaleUrl::route('properties.index') }}">{{ __('site.nav.properties') }}</a>
                <select class="language-select" aria-label="{{ __('site.language') }}" onchange="if (this.value) window.location.href = this.value;">
                    @foreach (\App\Support\Locales::supported() as $locale => $label)
                        <option value="{{ \App\Support\LocaleUrl::switchUrl($locale) }}" @selected($locale === $currentLocale)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </nav>
    </header>

    <main>{{ $slot }}</main>

    <footer class="footer">
        <div class="shell">{{ __('site.portal.footer') }}</div>
    </footer>
</body>
</html>
