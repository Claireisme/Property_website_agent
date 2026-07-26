@props(['agency' => null, 'title' => null, 'showPrincipalNote' => false])

@php
    $currentLocale = app()->getLocale();
    $watermarkEnabled = (bool) config('app.watermark.enabled', false);
    $watermarkText = trim((string) config('app.watermark.text', 'Demo website')) ?: 'Demo website';
@endphp

<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #111827;
            --muted: #5b6472;
            --line: #d9e1e8;
            --surface: #f7f9fb;
            --brand: #0f766e;
            --brand-dark: #134e4a;
            --accent: #c27803;
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
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar {
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.94);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav {
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            font-weight: 800;
            letter-spacing: 0;
        }

        .links {
            display: flex;
            align-items: center;
            gap: 18px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 650;
        }

        .filters-shell {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            margin-top: 24px;
            overflow: hidden;
        }

        .filters-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            cursor: pointer;
            font-weight: 850;
            list-style: none;
        }

        .filters-summary::-webkit-details-marker {
            display: none;
        }

        .filters-summary::after {
            content: "+";
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #ffffff;
            border: 1px solid var(--line);
        }

        .filters-shell[open] .filters-summary::after {
            content: "-";
        }

        .filters-panel {
            border-top: 1px solid var(--line);
            padding: 18px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .filters-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
        }

        .checkbox-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            margin-top: 12px;
        }

        .checkbox-row label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--ink);
        }

        .checkbox-row input {
            width: auto;
        }

        .location-combobox {
            position: relative;
            display: block;
        }

        .location-menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            z-index: 35;
            display: none;
            width: min(420px, 92vw);
            max-height: 330px;
            overflow: auto;
            border: 1px solid #93c5fd;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 18px 42px rgba(17, 24, 39, .18);
            padding: 8px 0;
        }

        .location-combobox.is-open .location-menu {
            display: block;
        }

        .location-option {
            width: 100%;
            min-height: 0;
            justify-content: flex-start;
            border-radius: 0;
            padding: 10px 16px;
            background: #ffffff;
            color: var(--ink);
            text-align: left;
            font-weight: 720;
        }

        .location-option:hover,
        .location-option:focus {
            background: #eef7f5;
            color: var(--brand-dark);
        }

        .location-option.is-child {
            padding-left: 34px;
            font-weight: 650;
        }

        .location-option[hidden] {
            display: none;
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

        .site-watermark {
            position: fixed;
            inset: -10vh -10vw;
            z-index: 2147483000;
            display: grid;
            grid-template-columns: repeat(6, minmax(180px, 1fr));
            grid-auto-rows: 120px;
            gap: 24px 20px;
            pointer-events: none;
            overflow: hidden;
            opacity: .68;
        }

        .site-watermark span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            color: rgba(17, 24, 39, .16);
            font-size: clamp(20px, 2.2vw, 36px);
            font-weight: 850;
            line-height: 1;
            white-space: nowrap;
            transform: rotate(-45deg);
            user-select: none;
        }

        .button,
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            min-height: 42px;
            padding: 0 16px;
            background: var(--brand);
            color: #ffffff;
            font: inherit;
            font-weight: 750;
            cursor: pointer;
        }

        .button.secondary {
            background: var(--ink);
        }

        .nav-toggle {
            display: none;
            width: 42px;
            min-height: 42px;
            flex: 0 0 42px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0;
            background: #ffffff;
            color: var(--ink);
            box-shadow: 0 8px 22px rgba(17, 24, 39, .06);
        }

        .nav-toggle-lines {
            display: grid;
            gap: 5px;
            width: 18px;
        }

        .nav-toggle-line {
            display: block;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            transition: transform .18s ease, opacity .18s ease;
        }

        .nav.is-open .nav-toggle-line:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }

        .nav.is-open .nav-toggle-line:nth-child(2) {
            opacity: 0;
        }

        .nav.is-open .nav-toggle-line:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        .hero {
            background:
                linear-gradient(115deg, rgba(17, 24, 39, 0.88), rgba(19, 78, 74, 0.64)),
                radial-gradient(circle at 84% 18%, rgba(194, 120, 3, 0.36), transparent 28%),
                linear-gradient(135deg, #12312e, #273244);
            color: #ffffff;
            padding: 76px 0 64px;
        }

        .hero-home {
            --hero-pan-x: 0px;
            --hero-pan-y: 0px;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: #0f172a;
        }

        .hero-home::before {
            content: "";
            position: absolute;
            inset: -28px;
            z-index: -2;
            background-image: var(--hero-image);
            background-position: center;
            background-size: cover;
            filter: saturate(1.04) contrast(1.04);
            transform: translate3d(var(--hero-pan-x), var(--hero-pan-y), 0) scale(1.04);
            transition: transform .35s ease-out;
            will-change: transform;
        }

        .hero-home::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(circle at 84% 18%, rgba(194, 120, 3, 0.22), transparent 32%),
                linear-gradient(105deg, rgba(10, 18, 32, 0.92), rgba(10, 30, 45, 0.74) 52%, rgba(10, 30, 45, 0.52)),
                linear-gradient(180deg, rgba(15, 23, 42, 0.2), rgba(15, 23, 42, 0.5));
        }

        .hero-home .hero-grid {
            position: relative;
            z-index: 1;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
            gap: 40px;
            align-items: end;
        }

        h1 {
            margin: 0;
            font-size: clamp(38px, 6vw, 66px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        h2 {
            margin: 0 0 18px;
            font-size: 28px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .hero p,
        .lead {
            color: rgba(255, 255, 255, 0.82);
            font-size: 18px;
            max-width: 650px;
        }

        .panel {
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 8px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-home .panel {
            border-color: rgba(255, 255, 255, 0.28);
            background: rgba(8, 22, 34, 0.36);
            box-shadow: 0 22px 52px rgba(15, 23, 42, 0.2);
            backdrop-filter: blur(12px);
        }

        .photo-hero {
            padding: 28px 0 0;
            background: #ffffff;
        }

        .property-context-links {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .property-related-searches {
            padding: 44px 0 50px;
            border-top: 1px solid #e6edf2;
            background:
                radial-gradient(circle at 12% 18%, rgba(15, 118, 110, .08), transparent 28%),
                linear-gradient(180deg, #ffffff, #f6faf9);
        }

        .property-related-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: end;
            margin-bottom: 18px;
        }

        .property-related-header h2 {
            margin: 0;
        }

        .property-related-header p {
            max-width: 460px;
            margin: 0;
            color: var(--muted);
            font-weight: 650;
        }

        .property-context-link {
            position: relative;
            display: grid;
            align-content: end;
            gap: 7px;
            min-height: 180px;
            border: 1px solid #d4dee7;
            border-radius: 8px;
            padding: 24px 26px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .92), rgba(255, 255, 255, .72)),
                #ffffff;
            color: #111827;
            box-shadow: 0 12px 30px rgba(17, 24, 39, .06);
            overflow: hidden;
            isolation: isolate;
        }

        .property-context-link::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background: var(--context-art);
            background-position: center;
            background-size: cover;
            opacity: .92;
        }

        .property-context-link::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, .78), rgba(255, 255, 255, .44) 52%, rgba(255, 255, 255, .16)),
                linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(15, 78, 74, .05));
            box-shadow: inset 5px 0 var(--brand);
        }

        .property-context-link .property-context-arrow {
            position: absolute;
            top: 16px;
            right: 18px;
            color: rgba(15, 78, 74, .5);
            font-size: 18px;
            font-weight: 900;
        }

        .property-context-link-area {
            --context-art: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='900' height='320' viewBox='0 0 900 320'%3E%3Crect width='900' height='320' fill='%23e7f3ef'/%3E%3Cpath d='M0 260 C120 228 190 278 306 248 C408 222 452 188 560 210 C672 234 728 164 900 196 L900 320 L0 320 Z' fill='%230f766e' opacity='.2'/%3E%3Cpath d='M84 230 L84 166 L126 136 L168 166 L168 230 Z M112 230 L112 194 L140 194 L140 230 Z' fill='%23134e4a' opacity='.24'/%3E%3Cpath d='M235 232 L235 144 L286 116 L337 144 L337 232 Z M260 232 L260 178 L312 178 L312 232 Z' fill='%23134e4a' opacity='.18'/%3E%3Cpath d='M430 236 L430 154 L462 154 L462 126 L500 126 L500 154 L532 154 L532 236 Z M454 236 L454 184 L508 184 L508 236 Z' fill='%23134e4a' opacity='.2'/%3E%3Cpath d='M664 232 C674 178 706 138 760 114 C815 139 842 180 852 232 Z M716 232 L716 176 L804 176 L804 232 Z' fill='%23134e4a' opacity='.18'/%3E%3Ccircle cx='760' cy='108' r='16' fill='%23c27803' opacity='.28'/%3E%3Cpath d='M580 92 C620 42 706 38 760 84 C822 136 802 214 744 242 C686 270 610 246 586 184 C572 148 556 124 580 92 Z' fill='none' stroke='%23134e4a' stroke-width='10' opacity='.16'/%3E%3C/svg%3E");
        }

        .property-context-link-budget {
            --context-art: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='900' height='320' viewBox='0 0 900 320'%3E%3Crect width='900' height='320' fill='%23f8f3e8'/%3E%3Crect x='500' y='54' width='300' height='180' rx='24' fill='none' stroke='%23c27803' stroke-width='16' opacity='.24'/%3E%3Ccircle cx='650' cy='144' r='46' fill='none' stroke='%23c27803' stroke-width='14' opacity='.2'/%3E%3Ctext x='628' y='166' font-family='Arial,sans-serif' font-size='70' font-weight='900' fill='%23134e4a' opacity='.2'%3E€%3C/text%3E%3Ctext x='94' y='214' font-family='Arial,sans-serif' font-size='180' font-weight='900' fill='%23134e4a' opacity='.13'%3E€%3C/text%3E%3Cpath d='M0 270 C118 232 198 250 300 218 C418 180 520 246 634 204 C728 170 820 166 900 186 L900 320 L0 320 Z' fill='%23c27803' opacity='.13'/%3E%3Cpath d='M540 92 L760 92 M540 198 L760 198' stroke='%23134e4a' stroke-width='9' opacity='.16'/%3E%3C/svg%3E");
        }

        .property-context-link:hover {
            border-color: rgba(15, 118, 110, .3);
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(17, 24, 39, .08);
        }

        .property-context-eyebrow {
            color: var(--brand-dark);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .property-context-link strong {
            padding-right: 28px;
            font-size: clamp(24px, 3vw, 36px);
            line-height: 1.15;
        }

        .property-context-link span:last-child {
            color: var(--muted);
            font-size: 13px;
            font-weight: 750;
        }

        .photo-browser {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 10px;
            min-height: 560px;
        }

        .photo-browser-main,
        .photo-browser-thumb {
            position: relative;
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            padding: 0;
            background: #111827;
            cursor: zoom-in;
        }

        .photo-browser-main img,
        .photo-browser-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .photo-browser-main {
            min-height: 560px;
        }

        .photo-browser-rail {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: 132px;
            gap: 10px;
            max-height: 560px;
            overflow-y: auto;
            padding-right: 2px;
        }

        .photo-browser-info {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 16px;
            border-radius: 8px;
            background: linear-gradient(90deg, rgba(17, 24, 39, .82), rgba(17, 24, 39, .34));
            color: #ffffff;
            text-align: left;
        }

        .photo-browser-title {
            display: block;
            margin: 0;
            max-width: 640px;
            font-size: clamp(24px, 3.1vw, 38px);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: 0;
        }

        .photo-browser-info .price {
            color: #ffffff;
        }

        .photo-count-pill {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, .92);
            color: var(--ink);
            font-size: 13px;
            font-weight: 850;
            white-space: nowrap;
        }

        .property-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 28px;
            align-items: start;
        }

        .property-summary {
            margin-bottom: 28px;
            border-color: #dbe5ec;
            background:
                linear-gradient(180deg, #ffffff, #fbfdfc),
                radial-gradient(circle at 100% 0%, rgba(15, 118, 110, .09), transparent 34%);
            box-shadow: 0 16px 42px rgba(17, 24, 39, .08);
        }

        .property-summary .card-body {
            padding: 22px;
        }

        .summary-header {
            display: grid;
            gap: 16px;
        }

        .summary-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .summary-chip-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            min-height: 42px;
        }

        .summary-chip-row .badge {
            align-items: center;
            min-height: 42px;
            padding: 0 14px;
            font-size: 13px;
        }

        .summary-status {
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            border: 1px solid rgba(15, 118, 110, .18);
            border-radius: 999px;
            padding: 0 14px;
            background: #ffffff;
            color: var(--brand-dark);
            font-size: 13px;
            font-weight: 850;
            line-height: 1;
        }

        .summary-status::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            display: inline-block;
            margin-right: 6px;
            background: var(--brand);
        }

        .summary-address {
            width: 100%;
            margin: 0;
            color: #111827;
            font-size: clamp(27px, 3vw, 36px);
            line-height: 1.18;
            font-weight: 900;
            letter-spacing: 0;
            overflow-wrap: anywhere;
        }

        .summary-tools {
            display: flex;
            gap: 8px;
            flex: 0 0 auto;
        }

        .summary-tool {
            width: 42px;
            min-height: 42px;
            padding: 0;
            border: 1px solid #d4dee7;
            border-radius: 8px;
            background: rgba(255, 255, 255, .78);
            color: #8b99a8;
            font-size: 22px;
            font-weight: 450;
            box-shadow: 0 10px 22px rgba(17, 24, 39, .05);
        }

        .summary-tool:hover {
            border-color: rgba(15, 118, 110, .32);
            color: var(--brand-dark);
            background: #ffffff;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: minmax(220px, .82fr) minmax(0, 1.18fr);
            gap: 14px;
            margin-top: 20px;
        }

        .summary-price-card {
            border: 1px solid rgba(15, 118, 110, .2);
            border-radius: 8px;
            padding: 16px;
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .1), rgba(255, 255, 255, .98)),
                #ffffff;
            box-shadow: inset 5px 0 0 var(--brand), 0 12px 26px rgba(15, 78, 74, .08);
        }

        .summary-label,
        .summary-fact-label {
            color: var(--muted);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .summary-price-line {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 7px;
            color: #111827;
            line-height: 1;
            font-weight: 950;
        }

        .summary-currency {
            color: var(--brand-dark);
            font-size: clamp(18px, 1.8vw, 24px);
            letter-spacing: .02em;
        }

        .summary-amount {
            color: #111827;
            font-size: clamp(32px, 3.7vw, 46px);
            letter-spacing: 0;
        }

        .summary-poa {
            font-size: clamp(28px, 3vw, 38px);
        }

        .summary-price-notes {
            display: grid;
            gap: 4px;
            margin-top: 10px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 780;
        }

        .summary-facts-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .summary-fact {
            display: grid;
            gap: 6px;
            min-height: 82px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, .86);
            color: #111827;
        }

        .summary-fact strong {
            display: block;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 20px;
            line-height: 1.1;
            font-weight: 900;
        }

        .summary-fact-type {
            grid-column: span 2;
        }

        .summary-fact-type strong {
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
        }

        .summary-fact-ber {
            overflow: visible;
            align-content: start;
        }

        .ber-rating-image {
            display: block;
            width: min(100%, 154px);
            height: auto;
            margin-top: 1px;
            border-radius: 5px;
            filter: drop-shadow(0 8px 14px rgba(17, 24, 39, .1));
        }

        .ber-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex: 0 0 auto;
            width: min(100%, 112px);
            min-width: 0;
            min-height: 32px;
            border-radius: 7px;
            padding: 6px 18px 6px 10px;
            color: #ffffff;
            font-weight: 950;
            clip-path: polygon(0 0, calc(100% - 15px) 0, 100% 50%, calc(100% - 15px) 100%, 0 100%);
            box-shadow: 0 8px 16px rgba(17, 24, 39, .1);
        }

        .ber-badge::after {
            display: none;
        }

        .ber-badge span {
            position: relative;
            z-index: 1;
            font-size: 12px;
            letter-spacing: .04em;
        }

        .ber-badge strong {
            position: relative;
            z-index: 1;
            font-size: 17px;
            line-height: 1;
        }

        .ber-a {
            background: #15803d;
        }

        .ber-b {
            background: #65a30d;
        }

        .ber-c {
            background: #ca8a04;
        }

        .ber-d {
            background: #ea580c;
        }

        .ber-e,
        .ber-f,
        .ber-g {
            background: #dc2626;
        }

        .ber-exempt {
            background: #64748b;
        }

        .summary-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(217, 225, 232, .88);
        }

        .property-side {
            position: sticky;
            top: 92px;
            display: grid;
            gap: 14px;
            align-self: start;
        }

        .map-card {
            position: sticky;
            top: 92px;
        }

        .property-side .map-card {
            position: static;
            top: auto;
        }

        .map-frame {
            width: 100%;
            aspect-ratio: 4 / 3;
            border: 0;
            display: block;
            background: #e5edf3;
        }

        .enquiry-card {
            position: relative;
            overflow: visible;
            border-color: rgba(194, 120, 3, .34);
            background:
                linear-gradient(135deg, rgba(255, 247, 230, .96), rgba(238, 247, 245, .92) 58%, #ffffff),
                #ffffff;
            box-shadow:
                0 16px 38px rgba(17, 24, 39, .09),
                0 0 0 4px rgba(194, 120, 3, .06);
        }

        .enquiry-success {
            display: grid;
            gap: 4px;
            border: 1px solid rgba(15, 118, 110, .26);
            border-radius: 8px;
            padding: 13px 15px;
            background: linear-gradient(135deg, #edf9f6, #ffffff);
            color: #14554f;
            box-shadow: 0 12px 28px rgba(15, 78, 74, .08);
        }

        .enquiry-success strong {
            font-size: 15px;
            line-height: 1.2;
        }

        .enquiry-success span {
            color: #3f6f69;
            font-size: 13px;
            line-height: 1.4;
        }

        .enquiry-summary {
            position: relative;
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            min-height: 78px;
            padding: 18px 16px;
            list-style: none;
            cursor: pointer;
        }

        .enquiry-summary::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            background: linear-gradient(180deg, var(--accent), var(--brand));
        }

        .enquiry-summary::-webkit-details-marker {
            display: none;
        }

        .enquiry-summary:focus {
            outline: 0;
        }

        .enquiry-summary:focus-visible {
            outline: 3px solid rgba(15, 118, 110, .22);
            outline-offset: 3px;
        }

        .enquiry-avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand));
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .02em;
            box-shadow: 0 10px 20px rgba(15, 78, 74, .18);
            overflow: hidden;
        }

        .enquiry-avatar img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .enquiry-summary-copy {
            min-width: 0;
        }

        .enquiry-summary-copy span {
            display: block;
            color: #7c4a03;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.2;
        }

        .enquiry-summary-copy strong {
            display: block;
            margin-top: 3px;
            color: var(--ink);
            font-size: 17px;
            line-height: 1.18;
            font-weight: 900;
            letter-spacing: 0;
        }

        .enquiry-summary-copy em {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-style: normal;
            font-weight: 750;
            line-height: 1.2;
        }

        .enquiry-summary-action {
            position: relative;
            display: grid;
            justify-items: center;
            gap: 9px;
            min-width: 72px;
        }

        .enquiry-summary-action::after {
            content: "";
            position: absolute;
            right: -2px;
            bottom: -2px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--accent);
            box-shadow: 0 0 0 0 rgba(194, 120, 3, .28);
            animation: enquiryDotPulse 1.75s ease-in-out infinite;
        }

        .enquiry-nudge-icon {
            position: relative;
            width: 25px;
            height: 20px;
            border: 2px solid var(--accent);
            border-radius: 7px;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(194, 120, 3, .16);
            animation: enquiryNudgePulse 1s ease-in-out infinite;
        }

        .enquiry-nudge-icon::before {
            content: "";
            position: absolute;
            left: 5px;
            right: 5px;
            top: 6px;
            height: 2px;
            border-radius: 999px;
            background: rgba(15, 118, 110, .74);
        }

        .enquiry-nudge-icon::after {
            content: "";
            position: absolute;
            right: 4px;
            bottom: -5px;
            width: 8px;
            height: 8px;
            border-right: 2px solid var(--accent);
            border-bottom: 2px solid var(--accent);
            background: #ffffff;
            transform: rotate(45deg);
        }

        .enquiry-action-open,
        .enquiry-action-close {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid rgba(15, 118, 110, .24);
            border-radius: 999px;
            padding: 8px 10px;
            background: #ffffff;
            color: var(--brand-dark);
            box-shadow: 0 8px 18px rgba(15, 78, 74, .08);
        }

        .enquiry-action-close {
            display: none;
        }

        .enquiry-chevron {
            display: inline-block;
            transition: transform .18s ease;
        }

        .enquiry-disclosure[open] .enquiry-summary {
            border-bottom: 1px solid #e2eaef;
        }

        .enquiry-disclosure[open] .enquiry-action-open {
            display: none;
        }

        .enquiry-disclosure[open] .enquiry-action-close {
            display: inline-flex;
        }

        .enquiry-disclosure[open] .enquiry-chevron {
            transform: rotate(180deg);
        }

        .enquiry-disclosure[open] .enquiry-nudge-icon,
        .enquiry-disclosure[open] .enquiry-summary-action::after {
            display: none;
        }

        @keyframes enquiryNudgePulse {
            0%, 100% {
                opacity: .55;
                transform: translateY(-2px);
            }

            45% {
                opacity: 1;
                transform: translateY(-6px);
            }
        }

        @keyframes enquiryDotPulse {
            0%, 100% {
                opacity: .35;
                box-shadow: 0 0 0 0 rgba(194, 120, 3, .28);
            }

            50% {
                opacity: 1;
                box-shadow: 0 0 0 8px rgba(194, 120, 3, 0);
            }
        }

        .enquiry-collapse-body {
            position: relative;
            overflow: hidden;
            max-height: 680px;
            opacity: 1;
            transform-origin: top;
            clip-path: inset(0 0 0 0);
            transition:
                max-height 1.8s cubic-bezier(.58, 0, .16, 1),
                opacity 1.15s ease,
                clip-path 1.8s cubic-bezier(.58, 0, .16, 1);
        }

        .enquiry-collapse-body::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 9px;
            opacity: 0;
            background: linear-gradient(180deg, rgba(15, 78, 74, .18), rgba(15, 78, 74, 0));
            transform: translateY(9px);
            transition: opacity .32s ease, transform 1.8s cubic-bezier(.58, 0, .16, 1);
            pointer-events: none;
        }

        .enquiry-disclosure.is-rolling-up .enquiry-collapse-body {
            max-height: 0;
            opacity: .08;
            clip-path: inset(0 0 100% 0);
        }

        .enquiry-disclosure.is-rolling-up .enquiry-collapse-body::after {
            opacity: 1;
            transform: translateY(0);
        }

        .property-enquiry-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 16px 18px 18px;
        }

        .property-enquiry-form label {
            font-size: 12px;
            color: #334155;
            font-weight: 850;
        }

        .property-enquiry-form textarea {
            min-height: 96px;
        }

        .enquiry-type-field {
            min-width: 0;
            margin: 0;
            border: 0;
            padding: 0;
        }

        .enquiry-type-field legend {
            margin: 0 0 6px;
            padding: 0;
            color: #334155;
            font-size: 12px;
            font-weight: 850;
        }

        .enquiry-type-options {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .enquiry-type-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0 9px;
            background: #ffffff;
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            line-height: 1;
            cursor: pointer;
        }

        .enquiry-type-option:has(input:checked) {
            border-color: rgba(15, 118, 110, .32);
            background: #eef7f5;
            color: var(--brand-dark);
        }

        .enquiry-type-option input {
            width: auto;
            margin: 0;
            accent-color: var(--brand);
        }

        .enquiry-privacy {
            border: 1px solid #e5edf2;
            border-radius: 8px;
            padding: 10px 11px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .amenity-list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .amenity-item {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px;
        }

        .amenity-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #eef7f5;
            color: var(--brand-dark);
            font-weight: 900;
        }

        .amenity-distance {
            color: var(--brand-dark);
            font-weight: 850;
            white-space: nowrap;
        }

        .band {
            padding: 48px 0;
        }

        .muted {
            color: var(--muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            overflow: hidden;
        }

        .google-reviews-band {
            padding: 40px 0;
            background: #eef2f4;
            border-bottom: 1px solid #dce5eb;
        }

        .google-reviews {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 22px;
            align-items: center;
        }

        .google-score-card {
            text-align: center;
            padding: 18px 16px;
        }

        .google-score-label {
            display: block;
            color: #111827;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .google-stars,
        .google-card-stars {
            display: block;
            color: #f4b400;
            letter-spacing: .04em;
            line-height: 1;
        }

        .google-stars {
            margin-top: 12px;
            font-size: 36px;
        }

        .google-card-stars {
            margin-top: 16px;
            font-size: 18px;
        }

        .google-review-count {
            display: block;
            margin-top: 10px;
            color: #111827;
            font-size: 16px;
        }

        .google-wordmark {
            display: block;
            margin-top: 8px;
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0;
        }

        .google-review-marquee {
            position: relative;
            overflow: hidden;
            padding: 4px 0;
            mask-image: linear-gradient(90deg, transparent, #000 7%, #000 93%, transparent);
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 7%, #000 93%, transparent);
        }

        .google-review-track {
            display: flex;
            width: max-content;
            gap: 18px;
            animation: googleReviewScroll 78s linear infinite;
            will-change: transform;
        }

        .google-review-marquee:hover .google-review-track,
        .google-review-marquee:focus-within .google-review-track {
            animation-play-state: paused;
        }

        .google-review-card {
            flex: 0 0 286px;
            min-height: 244px;
            border-radius: 8px;
            padding: 20px;
            background: rgba(255, 255, 255, .72);
            box-shadow: 0 14px 34px rgba(17, 24, 39, .06);
        }

        .google-review-head {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) 28px;
            gap: 12px;
            align-items: center;
        }

        .google-avatar {
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--avatar-color);
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
        }

        .google-review-head strong,
        .google-review-head small {
            display: block;
        }

        .google-review-head strong {
            color: #111827;
            font-size: 18px;
            line-height: 1.15;
        }

        .google-review-head small {
            margin-top: 2px;
            color: #6b7280;
            font-size: 14px;
        }

        .google-mini {
            font-size: 24px;
            font-weight: 900;
            color: #4285f4;
        }

        .google-review-card p {
            margin: 16px 0 0;
            color: #111827;
            font-size: 16px;
            line-height: 1.5;
        }

        @keyframes googleReviewScroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(calc(-50% - 9px));
            }
        }

        .about-hero {
            padding: 58px 0 42px;
            background:
                linear-gradient(115deg, rgba(17, 24, 39, .92), rgba(19, 78, 74, .76)),
                linear-gradient(135deg, #102523, #273244);
            color: #ffffff;
        }

        .about-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(360px, .98fr);
            gap: 38px;
            align-items: center;
        }

        .about-kicker {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            border-radius: 999px;
            padding: 0 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            color: rgba(255, 255, 255, .9);
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .about-hero h1 {
            margin: 16px 0 0;
            font-size: clamp(42px, 6vw, 70px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        .about-hero-copy {
            max-width: 650px;
            margin: 18px 0 0;
            color: rgba(255, 255, 255, .82);
            font-size: 18px;
        }

        .about-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .about-actions .button {
            background: #ffffff;
            color: var(--ink);
        }

        .about-actions .button.secondary {
            background: rgba(255, 255, 255, .12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, .24);
        }

        .about-portrait-panel {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            min-height: 520px;
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(255, 255, 255, .08);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .22);
        }

        .about-portrait-panel img {
            width: 100%;
            height: 100%;
            min-height: 520px;
            display: block;
            object-fit: cover;
        }

        .about-portrait-caption {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            border-radius: 8px;
            padding: 14px 16px;
            background: rgba(17, 24, 39, .76);
            color: #ffffff;
        }

        .about-portrait-caption strong {
            display: block;
            font-size: 17px;
        }

        .about-portrait-caption span {
            display: block;
            color: rgba(255, 255, 255, .75);
            font-size: 14px;
            margin-top: 2px;
        }

        .about-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .about-stat {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .08), #ffffff 52%),
                #ffffff;
        }

        .about-stat strong {
            display: block;
            color: var(--brand-dark);
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1;
        }

        .about-stat span {
            display: block;
            margin-top: 8px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .about-story {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 34px;
            align-items: start;
        }

        .about-section-label {
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .about-story h2,
        .team-section h2 {
            margin-top: 8px;
            color: #111827;
            font-size: clamp(30px, 4vw, 46px);
        }

        .about-copy p {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 17px;
        }

        .about-principles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }

        .about-principle {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            background: #ffffff;
        }

        .about-principle strong {
            display: block;
            color: #111827;
            font-size: 18px;
            line-height: 1.2;
        }

        .about-principle span {
            display: block;
            margin-top: 8px;
            color: var(--muted);
            font-size: 14px;
        }

        .team-section {
            background: #f7f9fb;
        }

        .team-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
        }

        .team-head p {
            max-width: 520px;
            margin: 0;
            color: var(--muted);
            font-size: 16px;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
        }

        .team-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(17, 24, 39, .06);
        }

        .team-photo {
            aspect-ratio: 4 / 5;
            background: #e8eef4;
        }

        .team-photo img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .team-body {
            padding: 14px 14px 16px;
        }

        .team-body h3 {
            margin: 0;
            color: #111827;
            font-size: 18px;
            line-height: 1.2;
        }

        .team-role {
            display: block;
            margin-top: 4px;
            color: var(--brand-dark);
            font-size: 13px;
            font-weight: 850;
        }

        .team-body p {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .photo {
            position: relative;
            aspect-ratio: 16 / 10;
            min-height: 0;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(15, 118, 110, 0.86), rgba(17, 24, 39, 0.78)),
                linear-gradient(45deg, #e8eef4, #ffffff);
            color: #ffffff;
            display: grid;
            font-weight: 800;
        }

        .photo::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            border: 14px solid rgba(16, 94, 88, .82);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, .2),
                inset 0 -42px 54px rgba(17, 24, 39, .16);
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
            box-shadow: 0 12px 24px rgba(17, 24, 39, .24);
        }

        .status-corner.available span {
            background: rgba(15, 118, 110, .9);
        }

        .status-corner.under_offer span {
            background: rgba(194, 120, 3, .9);
        }

        .status-corner.sale_agreed span {
            background: rgba(124, 58, 237, .9);
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
            min-height: 0;
            object-fit: cover;
            display: block;
            position: relative;
            z-index: 0;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            padding: 16px;
            display: flex;
            align-items: flex-end;
            position: relative;
            z-index: 2;
        }

        .gallery {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(220px, .7fr);
            gap: 10px;
            margin-bottom: 28px;
        }

        .gallery-main,
        .gallery-thumb {
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            padding: 0;
            background: #111827;
            cursor: zoom-in;
        }

        .gallery-main img,
        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-main {
            aspect-ratio: 16 / 10;
        }

        .gallery-thumbs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .gallery-thumb {
            aspect-ratio: 1 / 1;
            position: relative;
        }

        .gallery-count {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            background: rgba(17, 24, 39, .66);
            color: #ffffff;
            font-weight: 900;
        }

        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: none;
            grid-template-rows: auto minmax(0, 1fr) auto;
            background: rgba(17, 24, 39, .94);
            color: #ffffff;
        }

        .lightbox.is-open {
            display: grid;
        }

        .lightbox-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
        }

        .lightbox-stage {
            position: relative;
            display: grid;
            place-items: center;
            min-height: 0;
            padding: 0 72px;
        }

        .lightbox-stage img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 46px;
            min-height: 46px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
        }

        .lightbox-prev {
            left: 18px;
        }

        .lightbox-next {
            right: 18px;
        }

        .lightbox-filmstrip {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 12px 18px 18px;
        }

        .lightbox-filmstrip button {
            flex: 0 0 86px;
            width: 86px;
            min-height: 62px;
            padding: 0;
            border: 2px solid transparent;
            background: transparent;
            overflow: hidden;
        }

        .lightbox-filmstrip button.is-active {
            border-color: #ffffff;
        }

        .lightbox-filmstrip img {
            width: 100%;
            height: 58px;
            object-fit: cover;
            display: block;
        }

        .detail-copy {
            display: grid;
            gap: 14px;
            margin: 18px 0 0;
        }

        .property-description {
            color: var(--muted);
            font-size: 17px;
            line-height: 1.7;
        }

        .property-description p {
            margin: 0 0 14px;
        }

        .property-description p:last-child,
        .property-description ul:last-child,
        .property-description ol:last-child {
            margin-bottom: 0;
        }

        .property-description strong {
            color: var(--ink);
            font-weight: 850;
        }

        .property-description em {
            color: var(--ink);
        }

        .property-description ul,
        .property-description ol {
            margin: 0 0 14px;
            padding-left: 22px;
        }

        .property-description li + li {
            margin-top: 5px;
        }

        .feature-card {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 0;
            width: auto;
            max-width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #ffffff;
            color: var(--ink);
            font-size: 14px;
            font-weight: 750;
            line-height: 1;
            white-space: nowrap;
        }

        .feature-icon {
            flex: 0 0 24px;
            height: 24px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #eef7f5;
            color: var(--brand-dark);
            font-size: 14px;
        }

        .feature-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .feature-text {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .action-stack {
            display: grid;
            gap: 12px;
        }

        .modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            place-items: center;
            padding: 24px;
            background: rgba(17, 24, 39, .62);
        }

        .modal.is-open {
            display: grid;
        }

        .modal-card {
            width: min(720px, 100%);
            max-height: min(760px, calc(100vh - 48px));
            overflow: auto;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 26px 80px rgba(17, 24, 39, .28);
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 20px 22px;
            border-bottom: 1px solid var(--line);
        }

        .modal-body {
            padding: 22px;
        }

        .modal-eyebrow {
            display: block;
            margin-bottom: 4px;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .modal-head h2 {
            margin: 0;
            font-size: 28px;
            line-height: 1.05;
        }

        .bid-access-modal {
            width: 100%;
            max-width: 920px;
            max-height: calc(100vh - 32px);
            max-height: calc(100dvh - 32px);
            overflow: hidden;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr);
            border-radius: 14px;
            border: 1px solid rgba(11, 94, 83, .16);
        }

        .bid-access-modal .modal-head {
            padding: 18px 20px;
            background:
                linear-gradient(135deg, rgba(238, 247, 245, .95), rgba(255, 255, 255, .98));
        }

        .bid-access-modal .modal-body {
            display: grid;
            gap: 12px;
            min-height: 0;
            overflow: auto;
            padding: 16px 20px 20px;
        }

        .bid-access-note {
            display: grid;
            gap: 3px;
            padding: 12px 14px;
            border: 1px solid rgba(11, 94, 83, .14);
            border-left: 5px solid var(--brand);
            border-radius: 10px;
            background: #f7fcfb;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.35;
        }

        .bid-access-note strong {
            color: var(--ink);
            font-size: 15px;
        }

        .bid-access-flow {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .bid-access-flow li {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 8px;
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #ffffff;
            color: var(--ink);
            font-size: 13px;
            font-weight: 900;
            box-shadow: 0 10px 26px rgba(17, 24, 39, .06);
        }

        .bid-access-flow li span {
            display: grid;
            flex: 0 0 24px;
            width: 24px;
            height: 24px;
            place-items: center;
            border-radius: 999px;
            background: var(--brand);
            color: #ffffff;
            font-size: 12px;
        }

        .bid-access-flow li.is-complete {
            border-color: rgba(11, 94, 83, .28);
            background: #f0faf7;
            color: var(--brand-dark);
        }

        .bid-alert {
            padding: 11px 13px;
            border: 1px solid rgba(11, 94, 83, .18);
            border-radius: 10px;
            background: #effaf7;
            color: var(--brand-dark);
            font-size: 14px;
            font-weight: 800;
        }

        .bid-alert-error {
            border-color: rgba(190, 18, 60, .25);
            background: #fff1f2;
            color: #9f1239;
        }

        .property-toast {
            position: fixed;
            top: 22px;
            left: 50%;
            z-index: 90;
            display: flex;
            width: min(560px, calc(100vw - 32px));
            align-items: center;
            gap: 12px;
            transform: translateX(-50%);
            padding: 14px 18px;
            border: 1px solid rgba(11, 94, 83, .18);
            border-left: 5px solid var(--brand);
            border-radius: 14px;
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 22px 52px rgba(17, 24, 39, .22);
            color: var(--ink);
            font-size: 15px;
            font-weight: 900;
            line-height: 1.35;
            animation: property-toast-in .22s ease-out both;
        }

        .property-toast.is-leaving {
            animation: property-toast-out .24s ease-in both;
        }

        .property-toast-icon {
            display: inline-grid;
            width: 28px;
            height: 28px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 999px;
            background: var(--brand);
            color: #ffffff;
            font-size: 15px;
            font-weight: 950;
            box-shadow: 0 8px 20px rgba(11, 94, 83, .2);
        }

        @keyframes property-toast-in {
            from {
                opacity: 0;
                transform: translate(-50%, -10px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        @keyframes property-toast-out {
            from {
                opacity: 1;
                transform: translate(-50%, 0);
            }
            to {
                opacity: 0;
                transform: translate(-50%, -10px);
            }
        }

        .buyer-account-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 14px;
            align-items: start;
        }

        .buyer-account-card,
        .buyer-session-card,
        .buyer-status-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 14px 34px rgba(17, 24, 39, .07);
        }

        .buyer-account-card {
            padding: 16px;
            transition:
                border-color .18s ease,
                box-shadow .18s ease,
                opacity .18s ease,
                filter .18s ease,
                background-color .18s ease;
        }

        .buyer-account-card h3 {
            grid-column: 1 / -1;
            margin: 0 0 4px;
            font-size: 18px;
            line-height: 1.15;
        }

        .buyer-register-card,
        .buyer-login-card {
            display: grid;
            gap: 12px;
        }

        .buyer-register-card,
        .buyer-login-card {
            min-height: 100%;
        }

        .buyer-login-form,
        .buyer-password-reset,
        .buyer-password-reset-form {
            display: grid;
            gap: 12px;
        }

        .buyer-password-reset {
            margin-top: 4px;
            padding-top: 14px;
            border-top: 1px solid rgba(148, 163, 184, .28);
        }

        .buyer-password-reset summary {
            width: fit-content;
            cursor: pointer;
            color: var(--brand-dark);
            font-size: 14px;
            font-weight: 900;
        }

        .buyer-password-reset p {
            margin: -3px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .buyer-password-reset-form + .buyer-password-reset-form {
            margin-top: 2px;
            padding-top: 12px;
            border-top: 1px dashed rgba(148, 163, 184, .32);
        }

        .buyer-account-card.is-active {
            border-color: rgba(11, 94, 83, .42);
            box-shadow: 0 18px 42px rgba(11, 94, 83, .11);
        }

        .buyer-account-card.is-inactive {
            border-color: rgba(148, 163, 184, .34);
            background: #f8fafc;
            opacity: .48;
            filter: saturate(.62);
        }

        .buyer-register-card p,
        .buyer-login-card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .buyer-code-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: end;
        }

        .bid-inline-button {
            min-height: 42px;
            padding: 0 14px;
            border: 1px solid rgba(11, 94, 83, .3);
            border-radius: 10px;
            background: #eef8f5;
            color: var(--brand-dark);
            font: inherit;
            font-weight: 900;
            cursor: pointer;
        }

        .bid-inline-button:disabled,
        .bid-inline-button.is-cooling-down {
            border-color: rgba(148, 163, 184, .35);
            background: #eef2f7;
            color: #64748b;
            cursor: not-allowed;
        }

        .buyer-account-step {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            background: #eef8f5;
            color: var(--brand-dark);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .buyer-register-card .bid-field,
        .buyer-login-card .bid-field {
            display: grid;
            gap: 5px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .buyer-register-card input,
        .buyer-login-card input {
            min-height: 42px;
            padding: 10px 11px;
            border: 1px solid var(--line);
            border-radius: 10px;
            font: inherit;
        }

        .buyer-register-card .bid-submit {
            margin-top: 10px;
            justify-self: start;
        }

        .buyer-session-card {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            padding: 12px 14px;
        }

        .buyer-session-card span {
            display: grid;
            gap: 2px;
        }

        .buyer-session-card em {
            color: var(--muted);
            font-style: normal;
            font-size: 13px;
        }

        .buyer-status-card {
            display: grid;
            gap: 6px;
            padding: 13px 15px;
            border-left: 5px solid var(--brand);
        }

        .buyer-status-card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .buyer-status-approved {
            background: linear-gradient(135deg, #f0fdf4, #ffffff);
        }

        .bid-access-form {
            gap: 10px 12px;
        }

        .bid-access-form .bid-field {
            gap: 5px;
            color: var(--muted);
            font-size: 13px;
        }

        .bid-access-form input,
        .bid-access-form select,
        .bid-access-form textarea {
            min-height: 42px;
            padding: 10px 11px;
            border-radius: 10px;
        }

        .bid-access-form textarea {
            min-height: 70px;
            resize: vertical;
        }

        .bid-access-form input[type="file"] {
            padding: 9px 10px;
            font-size: 13px;
        }

        .bid-field-hint {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
        }

        .bid-consent {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.35;
        }

        .bid-consent input {
            flex: 0 0 auto;
            width: auto;
            min-height: 0;
            margin-top: 2px;
        }

        .bid-submit {
            min-height: 46px;
            border-radius: 10px;
            font-size: 16px;
        }

        @media (max-width: 560px) {
            .modal {
                padding: 10px;
            }

            .bid-access-modal {
                width: 100%;
                max-height: calc(100vh - 20px);
                max-height: calc(100dvh - 20px);
            }

            .bid-access-modal .modal-head,
            .bid-access-modal .modal-body {
                padding-left: 14px;
                padding-right: 14px;
            }

            .bid-access-flow {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .buyer-account-grid {
                grid-template-columns: 1fr;
            }

            .buyer-code-row {
                grid-template-columns: 1fr;
            }

            .bid-access-form {
                grid-template-columns: 1fr;
            }

            .bid-access-form .span-2 {
                grid-column: 1;
            }
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
            background: #eef7f5;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        label {
            display: grid;
            gap: 6px;
            font-size: 14px;
            color: var(--muted);
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px 13px;
            font: inherit;
            color: var(--ink);
            background: #ffffff;
        }

        textarea,
        .span-2 {
            grid-column: 1 / -1;
        }

        .notice {
            border: 1px solid #9bd6ca;
            background: #edf9f6;
            color: #14554f;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .principal-note {
            padding: 46px 0;
            border-top: 1px solid #dce5eb;
            background:
                radial-gradient(circle at 16% 0%, rgba(15, 118, 110, .12), transparent 30%),
                linear-gradient(180deg, #f7faf9, #edf3f2);
        }

        .principal-note-card {
            display: grid;
            grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
            gap: 32px;
            align-items: stretch;
            border: 1px solid #d4dee7;
            border-radius: 8px;
            padding: 18px;
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 18px 42px rgba(17, 24, 39, .08);
        }

        .principal-portrait {
            min-height: 360px;
            border-radius: 8px;
            overflow: hidden;
            background: #dfe8ec;
        }

        .principal-portrait img {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 360px;
            object-fit: cover;
            object-position: center top;
        }

        .principal-message {
            display: grid;
            align-content: center;
            gap: 18px;
            padding: clamp(18px, 3vw, 34px);
            color: #111827;
        }

        .principal-kicker {
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .principal-handwriting {
            margin: 0;
            max-width: 760px;
            color: #1f2937;
            font-family: "Bradley Hand", "Segoe Print", "Comic Sans MS", cursive;
            font-size: clamp(26px, 3.2vw, 42px);
            line-height: 1.25;
        }

        .principal-signoff {
            display: grid;
            gap: 4px;
            margin-top: 4px;
        }

        .principal-signature {
            color: var(--brand-dark);
            font-family: "Bradley Hand", "Segoe Print", "Comic Sans MS", cursive;
            font-size: clamp(30px, 3vw, 44px);
            line-height: 1;
        }

        .principal-role {
            color: var(--muted);
            font-weight: 750;
        }

        .footer {
            border-top: 1px solid var(--line);
            padding: 34px 0;
            background: #111827;
            color: rgba(255, 255, 255, .74);
            font-size: 14px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(220px, .9fr) minmax(240px, .9fr);
            gap: 28px;
            align-items: start;
        }

        .footer-brand {
            display: block;
            color: #ffffff;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.2;
        }

        .footer-trading {
            display: block;
            margin-top: 4px;
            color: rgba(255, 255, 255, .58);
            font-size: 13px;
        }

        .footer-list {
            display: grid;
            gap: 8px;
            margin-top: 16px;
        }

        .footer-item {
            display: grid;
            grid-template-columns: max-content max-content;
            gap: 10px;
            color: rgba(255, 255, 255, .72);
        }

        .footer-label {
            flex: 0 0 auto;
            min-width: 0;
            color: rgba(255, 255, 255, .46);
            font-weight: 750;
        }

        .footer a {
            color: #ffffff;
        }

        .footer h2 {
            margin: 0 0 14px;
            color: #ffffff;
            font-size: 16px;
            line-height: 1.2;
        }

        .footer-contact {
            display: grid;
            gap: 8px;
        }

        .footer-contact a,
        .footer-contact span {
            color: rgba(255, 255, 255, .74);
        }

        .footer-socials {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .footer-social {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .16);
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0;
            transition: transform .18s ease, background .18s ease, border-color .18s ease;
        }

        .footer-social-icon {
            width: 20px;
            height: 20px;
            display: block;
            fill: currentColor;
        }

        .footer-social--facebook .footer-social-icon {
            width: 18px;
            height: 18px;
        }

        .footer-social--youtube .footer-social-icon,
        .footer-social--linkedin .footer-social-icon {
            width: 22px;
            height: 22px;
        }

        .footer-social:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, .32);
            background: rgba(255, 255, 255, .14);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .48);
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .nav {
                min-height: 58px;
                align-items: center;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 10px 12px;
                padding: 8px 0;
            }

            .brand {
                flex: 1 1 auto;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .nav-toggle {
                display: inline-flex;
            }

            .links {
                display: none;
                width: 100%;
                align-items: stretch;
                flex-direction: column;
                gap: 0;
                padding: 8px 0 10px;
                border-top: 1px solid var(--line);
            }

            .nav.is-open .links {
                display: flex;
            }

            .links a {
                padding: 11px 0;
                border-bottom: 1px solid rgba(217, 225, 232, .72);
            }

            .hero-home::before {
                inset: -18px;
                background-position: 58% center;
            }

            .language-select {
                width: 100%;
                min-height: 44px;
                margin-top: 10px;
            }

            .hero-grid,
            .about-hero-grid,
            .about-story,
            .google-reviews,
            .principal-note-card,
            .property-context-links,
            .filters-grid,
            .photo-browser,
            .property-layout,
            .gallery,
            .grid,
            .form {
                grid-template-columns: 1fr;
            }

            .bid-access-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .property-related-header {
                align-items: start;
                flex-direction: column;
            }

            .about-portrait-panel,
            .about-portrait-panel img {
                min-height: 420px;
            }

            .about-stats,
            .about-principles {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .google-score-card {
                text-align: left;
                padding: 0;
            }

            .team-head {
                display: block;
            }

            .team-head p {
                margin-top: 10px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-bottom {
                flex-direction: column;
            }

            .summary-header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }

            .summary-tools {
                width: 100%;
                justify-content: flex-start;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .summary-facts-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .photo-browser,
            .photo-browser-main {
                min-height: 360px;
            }

            .photo-browser-rail {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                grid-auto-rows: 86px;
                max-height: none;
                overflow: visible;
            }

            .photo-browser-info {
                align-items: flex-start;
                flex-direction: column;
            }

            .property-side,
            .map-card {
                position: static;
            }

            .lightbox-stage {
                padding: 0 18px;
            }

            .lightbox-nav {
                top: auto;
                bottom: 18px;
                transform: none;
            }
        }

        @media (max-width: 560px) {
            .property-summary .card-body {
                padding: 18px;
            }

            .enquiry-summary {
                grid-template-columns: 44px minmax(0, 1fr);
            }

            .enquiry-summary-action {
                grid-column: 2;
                justify-self: start;
            }

            .property-enquiry-form,
            .enquiry-type-options {
                grid-template-columns: 1fr;
            }

            .bid-access-flow,
            .bid-access-form {
                grid-template-columns: 1fr;
            }

            .bid-access-form .span-2 {
                grid-column: 1;
            }

            .about-hero {
                padding: 42px 0 32px;
            }

            .about-hero h1 {
                font-size: clamp(36px, 11vw, 48px);
            }

            .about-portrait-panel,
            .about-portrait-panel img {
                min-height: 360px;
            }

            .about-stats,
            .about-principles,
            .team-grid {
                grid-template-columns: 1fr;
            }

            .google-reviews-band {
                padding: 30px 0;
            }

            .google-review-card {
                flex-basis: 264px;
            }

            .principal-note {
                padding: 30px 0;
            }

            .principal-note-card {
                padding: 12px;
            }

            .principal-portrait,
            .principal-portrait img {
                min-height: 320px;
            }

            .google-review-card {
                min-height: 0;
            }

            .summary-address {
                font-size: clamp(25px, 7.8vw, 32px);
            }

            .summary-chip-row {
                min-height: 40px;
            }

            .summary-chip-row .badge,
            .summary-status {
                min-height: 40px;
                padding-inline: 12px;
                font-size: 12px;
            }

            .summary-amount {
                font-size: clamp(32px, 10.5vw, 42px);
            }

            .summary-facts-grid {
                grid-template-columns: 1fr;
            }

            .summary-fact-type {
                grid-column: 1 / -1;
            }

            .summary-tools {
                gap: 8px;
            }

            .summary-tool {
                width: 42px;
                min-height: 42px;
                font-size: 24px;
            }

            .summary-fact {
                font-size: 17px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-home::before {
                transform: scale(1.04);
                transition: none;
            }

            .google-review-track {
                width: 100%;
                flex-wrap: wrap;
                animation: none;
                transform: none;
            }

            .google-review-card[aria-hidden="true"] {
                display: none;
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
        <nav class="shell nav" data-site-nav>
            <a class="brand" href="{{ \App\Support\LocaleUrl::route('home') }}">{{ $agency->name ?? config('app.name') }}</a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation" aria-label="{{ __('site.nav.menu') }}" data-site-nav-toggle>
                <span class="nav-toggle-lines" aria-hidden="true">
                    <span class="nav-toggle-line"></span>
                    <span class="nav-toggle-line"></span>
                    <span class="nav-toggle-line"></span>
                </span>
            </button>
            <div class="links" id="site-navigation">
                <a href="{{ \App\Support\LocaleUrl::route('properties.index') }}">{{ __('site.nav.properties') }}</a>
                <a href="{{ \App\Support\LocaleUrl::route('valuation') }}">{{ __('site.nav.valuation') }}</a>
                <a href="{{ \App\Support\LocaleUrl::route('mortgages') }}">{{ __('site.nav.mortgages') }}</a>
                <a href="{{ \App\Support\LocaleUrl::route('about') }}">{{ __('site.nav.about') }}</a>
                <a href="{{ \App\Support\LocaleUrl::route('contact') }}">{{ __('site.nav.contact') }}</a>
                <select class="language-select" aria-label="{{ __('site.language') }}" onchange="if (this.value) window.location.href = this.value;">
                    @foreach (\App\Support\Locales::supported() as $locale => $label)
                        <option value="{{ \App\Support\LocaleUrl::switchUrl($locale) }}" @selected($locale === $currentLocale)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    @php
        $footerCompanyName = $agency->name ?? config('app.name');
        $footerTradingName = $agency->trading_name ?? null;
        $footerRegistrationNumber = filled($agency?->company_registration_number) ? $agency->company_registration_number : '765432';
        $footerPsraNumber = $agency->psra_licence_number ?? '001234';
        $footerPhone = $agency->phone ?? '+353 1 555 0100';
        $footerEmail = $agency->email ?? 'hello@example-estates.test';
        $footerAddress = collect([
            $agency->address ?? '12 Main Street',
            $agency->county ?? 'Dublin',
            $agency->eircode ?? 'D02 TEST',
        ])->filter()->join(', ');
        $footerTelHref = 'tel:'.preg_replace('/[^+0-9]/', '', $footerPhone);
        $footerSocials = [
            ['label' => 'Facebook', 'brand' => 'facebook', 'url' => $agency->facebook_url ?? 'https://www.facebook.com/exampleestates'],
            ['label' => 'Instagram', 'brand' => 'instagram', 'url' => $agency->instagram_url ?? 'https://www.instagram.com/exampleestates'],
            ['label' => 'YouTube', 'brand' => 'youtube', 'url' => $agency->youtube_url ?? 'https://www.youtube.com/@exampleestates'],
            ['label' => 'TikTok', 'brand' => 'tiktok', 'url' => $agency->tiktok_url ?? 'https://www.tiktok.com/@exampleestates'],
            ['label' => 'LinkedIn', 'brand' => 'linkedin', 'url' => $agency->linkedin_url ?? 'https://www.linkedin.com/company/example-estates'],
            ['label' => 'X', 'brand' => 'x', 'url' => $agency->x_url ?? 'https://x.com/exampleestates'],
        ];
        $footerSocialIcons = [
            'facebook' => [
                'viewBox' => '0 0 24 24',
                'path' => 'M15.12 5.32h2.1V2.14A27.2 27.2 0 0 0 14.16 2c-3.02 0-5.09 1.9-5.09 5.38v3H5.74v3.55h3.33V22h4.08v-8.07h3.2l.5-3.55h-3.7V7.73c0-1.03.28-2.41 1.97-2.41Z',
            ],
            'instagram' => [
                'viewBox' => '0 0 24 24',
                'path' => 'M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm-.2 2A3.6 3.6 0 0 0 4 7.6v8.8A3.6 3.6 0 0 0 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6A3.6 3.6 0 0 0 16.4 4H7.6Zm9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z',
            ],
            'youtube' => [
                'viewBox' => '0 0 24 24',
                'path' => 'M23.5 6.2a3 3 0 0 0-2.12-2.12C19.5 3.58 12 3.58 12 3.58s-7.5 0-9.38.5A3 3 0 0 0 .5 6.2 31.3 31.3 0 0 0 0 12a31.3 31.3 0 0 0 .5 5.8 3 3 0 0 0 2.12 2.12c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3 3 0 0 0 2.12-2.12A31.3 31.3 0 0 0 24 12a31.3 31.3 0 0 0-.5-5.8ZM9.75 15.55v-7.1L16 12l-6.25 3.55Z',
            ],
            'tiktok' => [
                'viewBox' => '0 0 24 24',
                'path' => 'M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 1 1-2.89-2.89c.3 0 .59.04.88.13V9.4a6.33 6.33 0 1 0 5.46 6.27V8.78a8.16 8.16 0 0 0 4.77 1.52V6.69Z',
            ],
            'linkedin' => [
                'viewBox' => '0 0 24 24',
                'path' => 'M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04s-2.14 1.45-2.14 2.94v5.67H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.07 2.07 0 1 1 0-4.14 2.07 2.07 0 0 1 0 4.14Zm1.78 13.02H3.56V9h3.56v11.45ZM22.22 0H1.77C.8 0 0 .77 0 1.72v20.56C0 23.23.8 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0Z',
            ],
            'x' => [
                'viewBox' => '0 0 24 24',
                'path' => 'M18.9 1.15h3.68l-8.04 9.2L24 22.85h-7.4l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.25 6.93 6.06-6.93Zm-1.3 19.5h2.04L6.49 3.25H4.3l13.3 17.4Z',
            ],
        ];
    @endphp

    @if ($showPrincipalNote)
        <section class="principal-note" aria-label="Message from the managing director">
            <div class="shell principal-note-card">
                <div class="principal-portrait">
                    <img src="{{ asset('images/team/patrick-doyle.jpg') }}" alt="Patrick Doyle, Managing Director" loading="lazy">
                </div>
                <div class="principal-message">
                    <span class="principal-kicker">A note from our managing director</span>
                    <p class="principal-handwriting">
                        Every client deserves straight advice, careful preparation, and a team that treats their move with real responsibility from the first conversation to the final handover.
                    </p>
                    <div class="principal-signoff">
                        <span class="principal-signature">Patrick Doyle</span>
                        <span class="principal-role">Managing Director, {{ $footerCompanyName }}</span>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <footer class="footer">
        <div class="shell footer-grid">
            <section>
                <span class="footer-brand">{{ $footerCompanyName }}</span>
                @if ($footerTradingName)
                    <span class="footer-trading">Trading as {{ $footerTradingName }}</span>
                @endif
                <div class="footer-list">
                    <div class="footer-item">
                        <span class="footer-label">Company No.</span>
                        <span>{{ $footerRegistrationNumber }}</span>
                    </div>
                    <div class="footer-item">
                        <span class="footer-label">PSRA Number</span>
                        <span>{{ $footerPsraNumber }}</span>
                    </div>
                </div>
            </section>

            <section>
                <h2>Contact</h2>
                <div class="footer-contact">
                    <a href="{{ $footerTelHref }}">{{ $footerPhone }}</a>
                    <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>
                    <span>{{ $footerAddress }}</span>
                </div>
            </section>

            <section>
                <h2>Follow us</h2>
                <div class="footer-socials">
                    @foreach ($footerSocials as $social)
                        @if ($social['url'])
                            @php($socialIcon = $footerSocialIcons[$social['brand']] ?? null)
                            <a class="footer-social footer-social--{{ $social['brand'] }}" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}">
                                @if ($socialIcon)
                                    <svg class="footer-social-icon footer-social-icon--{{ $social['brand'] }}" viewBox="{{ $socialIcon['viewBox'] }}" aria-hidden="true" focusable="false">
                                        <path d="{{ $socialIcon['path'] }}"></path>
                                    </svg>
                                @else
                                    <span class="footer-social-fallback">{{ $social['label'] }}</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        </div>
        <div class="shell footer-bottom">
            <span>&copy; {{ now()->year }} {{ $footerCompanyName }}. All rights reserved.</span>
            <span>Property services, valuations, lettings, and online enquiries.</span>
        </div>
    </footer>

    @if ($watermarkEnabled)
        <div class="site-watermark" aria-hidden="true">
            @foreach (range(1, 96) as $index)
                <span>{{ $watermarkText }}</span>
            @endforeach
        </div>
    @endif

    <script>
        (() => {
            const nav = document.querySelector('[data-site-nav]');
            const toggle = nav?.querySelector('[data-site-nav-toggle]');
            const links = nav?.querySelector('#site-navigation');

            if (!nav || !toggle || !links) {
                return;
            }

            const desktopQuery = window.matchMedia('(min-width: 821px)');
            const setOpen = (isOpen) => {
                nav.classList.toggle('is-open', isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
            };

            toggle.addEventListener('click', () => {
                setOpen(!nav.classList.contains('is-open'));
            });

            links.addEventListener('click', (event) => {
                if (!desktopQuery.matches && event.target.closest('a')) {
                    setOpen(false);
                }
            });

            desktopQuery.addEventListener('change', (event) => {
                if (event.matches) {
                    setOpen(false);
                }
            });
        })();

        (() => {
            const heroes = document.querySelectorAll('[data-hero-parallax]');

            if (!heroes.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            heroes.forEach((hero) => {
                let frame = null;

                const setPosition = (event) => {
                    const rect = hero.getBoundingClientRect();
                    const x = ((event.clientX - rect.left) / rect.width - 0.5) * 16;
                    const y = ((event.clientY - rect.top) / rect.height - 0.5) * 10;

                    hero.style.setProperty('--hero-pan-x', `${x.toFixed(2)}px`);
                    hero.style.setProperty('--hero-pan-y', `${y.toFixed(2)}px`);
                };

                const handleMove = (event) => {
                    if (frame) {
                        cancelAnimationFrame(frame);
                    }

                    frame = requestAnimationFrame(() => {
                        setPosition(event);
                        frame = null;
                    });
                };

                const resetPosition = () => {
                    if (frame) {
                        cancelAnimationFrame(frame);
                        frame = null;
                    }

                    hero.style.setProperty('--hero-pan-x', '0px');
                    hero.style.setProperty('--hero-pan-y', '0px');
                };

                hero.addEventListener('pointermove', handleMove);
                hero.addEventListener('mousemove', handleMove);
                hero.addEventListener('pointerleave', resetPosition);
                hero.addEventListener('mouseleave', resetPosition);
            });
        })();
    </script>
</body>
</html>
