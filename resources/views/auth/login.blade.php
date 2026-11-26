<!DOCTYPE html>
<html lang="id" data-no-debugbar="1">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - {{ config('app.name', 'Kinerja PT. SAC') }}</title>
    <style>
        html[data-no-debugbar] .phpdebugbar,
        html[data-no-debugbar] #phpdebugbar,
        html[data-no-debugbar] .phpdebugbar-openhandler {
            display: none !important
        }
    </style>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --ink: #F3F4EF;
            --text: #10131C;
            --muted: #6D7383;
            --line: #E8E9EE;
            --lime: #c8ff57;
            --brand: #2B59FF;
            --brand-dark: #1B45E0;
            --cyan: #67D6FF;
            --paper: #FAFAF8;
            --ease: cubic-bezier(.32, .72, 0, 1);
            --radius: 14px;
            --font-head: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
            --bg: #0a0a0f;
            --surface: #111118;
            --border: rgba(255, 255, 255, 0.08);
            --text: #f0eef9;
            --muted: #8884a0;
            ~
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            background: var(--paper);
            color: var(--text);
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Shell ---------- */
        .shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            min-height: 100vh;
            min-height: 100dvh;
        }

        /* ---------- Panel brand (>=900px) ---------- */
        .panel {
            display: none;
            position: relative;
            overflow: hidden;
            padding: 40px 40px 34px;
            background: linear-gradient(158deg, #F5F5F0 0%, #E9EEF9 100%);
            color: var(--text);
        }

        .panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(680px 460px at 12% -6%, rgba(43, 89, 255, .13), transparent 64%),
                radial-gradient(560px 420px at 92% 96%, rgba(38, 168, 214, .10), transparent 62%);
            pointer-events: none;
        }

        .panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(12, 16, 23, .05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(12, 16, 23, .05) 1px, transparent 1px);
            background-size: 84px 84px;
            -webkit-mask-image: radial-gradient(70% 60% at 30% 20%, #000 0%, transparent 78%);
            mask-image: radial-gradient(70% 60% at 30% 20%, #000 0%, transparent 78%);
            pointer-events: none;
        }

        .panel>* {
            position: relative;
            z-index: 1;
        }

        .panel-inner {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            gap: 40px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-shell {
            padding: 6px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .85);
            box-shadow: inset 0 0 0 1px rgba(12, 16, 23, .06);
        }

        .brand-core {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 14px;
            background: #fff;
        }

        .brand-core img {
            width: 26px;
            height: 26px;
            object-fit: contain;
        }

        .brand-text {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: .02em;
            color:rgba(0, 0, 0, .8);
        }

        .brand-text span {
            display: block;
            margin-top: 2px;
            font-size: 9px;
            font-weight: 500;
            letter-spacing: .22em;
            text-transform: uppercase;
            color: #8A8F9C;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: #6D7383;
            background: rgba(255, 255, 255, .78);
            box-shadow: inset 0 0 0 1px rgba(12, 16, 23, .06);
        }

        .pill i {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--brand);
            box-shadow: 0 0 10px rgba(43, 89, 255, .5);
        }

        .panel h1 {
            margin: 22px 0 0;
            font-size: clamp(40px, 3.7vw, 56px);
            font-weight: 800;
            letter-spacing: -.045em;
            line-height: .98;
            background: linear-gradient(180deg, #0C1017 38%, #5A6782 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .panel h1 b {
            font-weight: 800;
            background: linear-gradient(92deg, var(--brand), #1E9BC9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .lede {
            margin: 18px 0 0;
            max-width: 34ch;
            font-size: 13.5px;
            font-weight: 400;
            line-height: 1.75;
            color: #6D7383;
        }

        .facts {
            list-style: none;
            margin: 38px 0 0;
            padding: 0;
            max-width: 340px;
        }

        .facts li {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 13px 0;
            font-size: 12.5px;
            font-weight: 500;
            color: #565C6B;
            border-top: 1px solid rgba(12, 16, 23, .08);
            transition: color .5s var(--ease);
        }

        .facts li:last-child {
            border-bottom: 1px solid rgba(12, 16, 23, .08);
        }

        .facts li:hover {
            color: var(--text);
        }

        .facts i {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--brand);
            box-shadow: 0 0 8px rgba(43, 89, 255, .45);
        }

        .panel-foot {
            font-size: 9px;
            font-weight: 500;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: #A6ABB6;
        }

        /* ---------- Hero mobile ---------- */
        .hero-mobile {
            position: relative;
            isolation: isolate;

            /* PENTING: jangan hidden */
            overflow: visible;

            padding: 34px 22px 66px;

            background: transparent;
            color: var(--text);
            text-align: left;
        }

        /* Background utama */
        .hero-mobile::before {
            content: "";
            position: absolute;

            top: 0;
            left: 0;
            right: 0;

            /* Perpanjang background ke bawah */
            bottom: -160px;

            z-index: -1;

            background:
                radial-gradient(460px 320px at 18% -20%,
                    rgba(43, 89, 255, .16),
                    transparent 62%),
                radial-gradient(380px 300px at 96% 110%,
                    rgba(38, 168, 214, .12),
                    transparent 58%),
                linear-gradient(162deg,
                    #F6F6F1 0%,
                    #E8EEFA 100%);

            /* Fade ke background halaman */
            -webkit-mask-image: linear-gradient(to bottom,
                    #000 0%,
                    #000 45%,
                    rgba(0, 0, 0, .8) 65%,
                    rgba(0, 0, 0, .35) 82%,
                    transparent 100%);

            mask-image: linear-gradient(to bottom,
                    #000 0%,
                    #000 45%,
                    rgba(0, 0, 0, .8) 65%,
                    rgba(0, 0, 0, .35) 82%,
                    transparent 100%);

            pointer-events: none;
        }

        /* Grid pattern */
        .hero-mobile::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;

            background-image:
                linear-gradient(to right, rgba(12, 16, 23, .05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(12, 16, 23, .05) 1px, transparent 1px);

            background-size: 64px 64px;

            -webkit-mask-image: radial-gradient(80% 70% at 20% 0%,
                    #000 0%,
                    transparent 80%);

            mask-image: radial-gradient(80% 70% at 20% 0%,
                    #000 0%,
                    transparent 80%);

            pointer-events: none;
        }

        .hero-mobile>* {
            position: relative;
            z-index: 1;
        }

        .hero-mobile h1 {
            margin: 20px 0 0;
            font-size: clamp(27px, 7.6vw, 32px);
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1.04;
            background: linear-gradient(180deg, #0C1017 36%, #5A6782 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-mobile p {
            margin: 10px 0 0;
            font-size: 12.5px;
            font-weight: 400;
            line-height: 1.6;
            color: #6D7383;
        }

        /* ---------- Form ---------- */
        .content {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
        }

        .form-stage {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            margin-top: -44px;
            padding: 0 18px 44px;
        }

        .form-wrap {
            width: 100%;
            max-width: 416px;
        }

        /* Double-bezel card */
        .card-shell {
            border-radius: 27px;
            box-shadow: 2.5px 2.5px 7px #0000003f;
        }

        .card {
            border-radius: 27px;
            background: #fff;
            padding: 28px 24px 26px;
        }

        .card-head {
            margin-bottom: 22px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #8A8F9C;
            background: rgba(9, 13, 25, .04);
            box-shadow: inset 0 0 0 1px rgba(9, 13, 25, .05);
        }

        .card-head h2 {
            margin: 12px 0 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.035em;
            color: var(--text);
        }

        .card-head p {
            margin: 6px 0 0;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.6;
            color: var(--muted);
        }

        .label {
            display: block;
            margin-bottom: 8px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: #8A8F9C;
        }

        .field {
            width: 100%;
            height: 52px;
            padding: 0 15px;
            border: 1px solid var(--line);
            border-radius: 15px;
            background: #fff;
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            transition: border-color .45s var(--ease), box-shadow .45s var(--ease);
        }

        .field::placeholder {
            color: #A6ABB7;
            font-weight: 400;
        }

        .field:hover {
            border-color: #D8DAE2;
        }

        .field:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(43, 89, 255, .11);
        }

        .field.is-invalid {
            border-color: #E5484D;
        }

        .field.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(229, 72, 77, .12);
        }

        .field-group {
            position: relative;
        }

        .field-group .field {
            padding-right: 52px;
        }

        .eye {
            position: absolute;
            top: 50%;
            right: 8px;
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #A0A5B1;
            cursor: pointer;
            transform: translateY(-50%);
            transition: color .4s var(--ease), background .4s var(--ease);
        }

        .eye:hover {
            color: #10131C;
            background: rgba(9, 13, 25, .05);
        }

        .eye svg {
            width: 17px;
            height: 17px;
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .check {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
        }

        .check input {
            width: 17px;
            height: 17px;
            margin: 0;
            border-radius: 5px;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .check span {
            font-size: 12px;
            font-weight: 500;
            color: #5C6272;
        }

        .link {
            font-size: 12px;
            font-weight: 600;
            color: var(--brand);
            text-decoration: none;
            background-image: linear-gradient(currentColor, currentColor);
            background-size: 0% 1px;
            background-position: 0 100%;
            background-repeat: no-repeat;
            transition: background-size .5s var(--ease);
        }

        .link:hover {
            background-size: 100% 1px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 15px;
            letter-spacing: -0.01em;
            border: none;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            -webkit-font-smoothing: antialiased;
        }

        .btn:active {
            transform: scale(0.97) !important;
        }

        .btn-primary {
            background: var(--brand);
            color: var(--ink);
            padding: 15px 32px;
            border-radius: var(--radius);
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.01em;
            box-shadow: 0 0 0 0 rgba(200, 255, 87, 0);
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(200, 255, 87, 0.38);
            filter: brightness(1.06);
        }

        .btn-primary:hover::after {
            opacity: 1;
        }

        .btn:disabled {
            cursor: progress;
            opacity: .82;
        }

        .btn-ico {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            transition: transform .55s var(--ease);
        }

        .btn:hover .btn-ico {
            transform: translate(3px, -1px) scale(1.06);
        }

        .btn-ico svg {
            width: 14px;
            height: 14px;
        }

        .card-foot {
            margin: 18px 0 0;
            font-size: 12px;
            font-weight: 400;
            color: var(--muted);
            text-align: center;
        }

        .legal {
            margin: 18px 0 0;
            font-size: 10.5px;
            line-height: 1.8;
            color: #949AA6;
            text-align: center;
        }

        .legal b {
            color: #5C6272;
            font-weight: 600;
        }

        .alert {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 500;
            color: #1D4ED8;
            background: rgba(43, 89, 255, .07);
            box-shadow: inset 0 0 0 1px rgba(43, 89, 255, .12);
        }

        .err {
            margin-top: 7px;
            font-size: 11.5px;
            font-weight: 500;
            color: #D93A3F;
        }

        /* ---------- Entry motion ---------- */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .r {
            animation: rise .85s var(--ease) both;
        }

        .r2 {
            animation-delay: .07s;
        }

        .r3 {
            animation-delay: .13s;
        }

        .r4 {
            animation-delay: .19s;
        }

        .r5 {
            animation-delay: .25s;
        }

        .r6 {
            animation-delay: .31s;
        }

        @media (prefers-reduced-motion:reduce) {
            .r {
                animation: none !important;
            }

            .btn,
            .btn-ico,
            .field,
            .link,
            .eye {
                transition: none !important;
            }
        }

        /* ---------- Breakpoints ---------- */
        @media (min-width:900px) {
            .shell {
                grid-template-columns: minmax(0, 47%) minmax(0, 1fr);
            }

            .panel {
                display: flex;
            }

            .hero-mobile {
                display: none;
            }

            .form-stage {
                margin-top: 0;
                padding: 56px 44px;
            }

            .card {
                padding: 30px 28px 28px;
            }
        }

        #login-splash {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            overflow: hidden;
            background: #FAFAF7;
            transition: opacity .55s var(--ease), visibility .55s;
        }

        #login-splash::before,
        #login-splash::after {
            content: '';
            position: absolute;
            width: 70vw;
            height: 70vw;
            max-width: 680px;
            max-height: 680px;
            border-radius: 50%;
            filter: blur(8px);
            pointer-events: none;
        }

        #login-splash::before {
            top: -38%;
            left: 18%;
            background: radial-gradient(circle, rgba(43, 89, 255, .16), transparent 68%);
        }

        #login-splash::after {
            right: -34%;
            bottom: -42%;
            background: radial-gradient(circle, rgba(38, 168, 214, .15), transparent 68%);
        }

        #login-splash.is-leaving {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        #login-splash .splash-inner {
            position: relative;
            z-index: 1;
            width: min(370px, 84vw);
            text-align: center;
        }

        #login-splash .logo-orbit {
            position: relative;
            width: 132px;
            height: 132px;
            margin: 0 auto 30px;
            display: grid;
            place-items: center;
        }

        #login-splash .logo-orbit::before,
        #login-splash .logo-orbit::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 1px solid rgba(43, 89, 255, .18);
            border-radius: 38%;
            transform: rotate(45deg);
            animation: orbit 4s var(--ease) infinite;
        }

        #login-splash .logo-orbit::after {
            inset: 12px;
            border-color: rgba(38, 168, 214, .22);
            transform: rotate(-45deg);
            animation-duration: 3.2s;
            animation-direction: reverse;
        }

        #login-splash img {
            position: relative;
            z-index: 1;
            width: 96px;
            height: 96px;
            object-fit: contain;
            border-radius: 29px;
            background: rgba(255, 255, 255, .92);
            padding: 16px;
            box-shadow: 0 18px 45px -24px rgba(43, 89, 255, .55), inset 0 1px 1px #fff;
            animation: logo-in .8s var(--ease) both;
        }

        #login-splash h1 {
            margin: 0;
            font-size: clamp(27px, 7.4vw, 38px);
            font-weight: 800;
            letter-spacing: -.045em;
            animation: rise .8s var(--ease) .1s both;
        }

        #login-splash h1 span {
            background: linear-gradient(92deg, #2B59FF, #26A8D6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        #login-splash p {
            margin: 11px 0 26px;
            color: #7A8090;
            font-size: 11.5px;
            animation: rise .8s var(--ease) .18s both;
        }

        #login-splash .loader-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 9px;
            color: #7A8090;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        #login-splash .pct {
            color: #10131C;
            font-variant-numeric: tabular-nums;
        }

        #login-splash .track {
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(12, 16, 23, .07);
            box-shadow: inset 0 0 0 1px rgba(12, 16, 23, .035);
        }

        #login-splash .fill {
            position: relative;
            width: 100%;
            height: 100%;
            transform: scaleX(0);
            transform-origin: left;
            border-radius: inherit;
            background: linear-gradient(90deg, #2B59FF, #6E8CFF, #26A8D6);
            transition: transform 1.6s var(--ease);
            box-shadow: 0 0 18px rgba(43, 89, 255, .3);
        }

        #login-splash .fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .7), transparent);
            transform: translateX(-100%);
            animation: sweep 1.3s var(--ease) infinite;
        }

        #login-splash .splash-note {
            margin-top: 25px;
            color: #A0A5B0;
            font-size: 10px;
            letter-spacing: .12em;
            text-transform: uppercase;
            animation: rise .8s var(--ease) .25s both;
        }

        @keyframes orbit {
            to {
                transform: rotate(405deg);
            }
        }

        @keyframes logo-in {
            from {
                opacity: 0;
                transform: scale(.72) rotate(-8deg);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes sweep {
            to {
                transform: translateX(100%);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            #login-splash *,
            #login-splash::before,
            #login-splash::after {
                animation: none !important;
                transition: none !important;
            }
        }

        @media (hover: none) and (pointer: coarse) {
            .field {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    @if (!request()->boolean('show'))
        <div id="login-splash" role="status" aria-label="Memuat aplikasi">
            <div class="splash-inner">
                <div class="logo-orbit"><img src="{{ asset('logo/logo_admin_splash.png') }}" alt="Logo Kinerja SAC-PO">
                </div>
                <h1>KINERJA <span>SAC-PO</span></h1>
                <p>Menyiapkan ruang kerja Anda</p>
                <div class="loader-meta"><span id="splash-status">Menghubungkan sistem</span><span class="pct"
                        id="splash-pct">0%</span></div>
                <div class="track">
                    <div class="fill"></div>
                </div>
                <div class="splash-note">Sistem Kinerja &amp; Absensi</div>
            </div>
        </div>
    @endif
    <div class="shell">

        {{-- ================= Panel brand: desktop ================= --}}
        <aside class="panel">
            <div class="panel-inner">
                <div class="brand">
                    <div>
                        <div class="w-1/2">
                            <img src="{{ asset('logo/logo_full.png') }}" alt="Logo Kinerja PT. SAC">
                        </div>
                    </div>
                </div>

                <div>
                    <span class="pill"><i></i> Akses Internal</span>
                    <h1>Satu tempat<br>untuk <b>kinerja</b><br>tim Anda.</h1>
                    <p class="lede">
                        Absensi, penilaian kinerja, dan rekap tim dalam satu sistem yang rapi — tanpa
                        spreadsheet yang berserakan.
                    </p>

                    <ul class="facts">
                        <li><i></i> Absensi dan kehadiran harian</li>
                        <li><i></i> Penilaian dan rekap kinerja</li>
                        <li><i></i> Pengajuan izin dan lembur</li>
                    </ul>
                </div>

                <p class="panel-foot">&copy; {{ date('Y') }} PT. Surya Amanah Cendekia</p>
            </div>
        </aside>

        {{-- ================= Konten ================= --}}
        <div class="content">

            {{-- Hero: mobile --}}
            <header class="hero-mobile">
                <div class="brand">
                    <div>
                        <div class="max-w-screen">
                            <img src="{{ asset('logo/logo_full.png') }}" alt="Logo Kinerja PT. SAC">
                        </div>
                    </div>
                </div>

                <h1>Masuk ke<br>Akun Anda</h1>
                <p>Masukkan nama pengguna dan kata sandi untuk melanjutkan.</p>
            </header>

            <main class="form-stage">
                <div class="form-wrap">

                    @if (session('status'))
                        <div class="alert r">{{ session('status') }}</div>
                    @endif

                    <div class="card-shell r r2">
                        <div class="card">
                            <div class="card-head">
                                <h2 class="text-black! mt-0!">Masuk</h2>
                                <p>Gunakan akun yang diberikan administrator.</p>
                            </div>

                            <form method="POST" action="{{ route('login') }}" id="loginForm">
                                @csrf

                                <div class="r r3">
                                    <label for="name" class="label required">Nama Pengguna</label>
                                    <input id="name" name="name" type="text" required autofocus
                                        autocomplete="username" value="{{ old('name') }}"
                                        placeholder="Masukkan nama pengguna"
                                        class="field text-black! @error('name') is-invalid @enderror">
                                    @error('name')
                                        <p class="err">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="r r4" style="margin-top:16px" x-data="{ show: false }">
                                    <label for="password" class="label required">Kata Sandi</label>
                                    <div class="field-group">
                                        <input id="password" name="password" :type="show ? 'text' : 'password'"
                                            required autocomplete="current-password" placeholder="Masukkan kata sandi"
                                            class="field text-black! @error('password') is-invalid @enderror">
                                        <button type="button" class="eye" @click="show = !show"
                                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                            <svg x-show="!show" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                                aria-hidden="true">
                                                <path
                                                    d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <svg x-show="show" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                                aria-hidden="true" style="display:none">
                                                <path d="M3 3l18 18" />
                                                <path
                                                    d="M10.6 6.7A9.9 9.9 0 0 1 12 6.5c6 0 9.5 5.5 9.5 5.5a17 17 0 0 1-2.7 3.2" />
                                                <path
                                                    d="M6.2 8.3A17 17 0 0 0 2.5 12S6 17.5 12 17.5c1.2 0 2.3-.2 3.3-.6" />
                                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="err">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="row r r5">
                                    <label class="check" for="remember">
                                        <input id="remember" name="remember" type="checkbox">
                                        <span>Ingat saya</span>
                                    </label>

                                    <a href="{{ route('password.request') }}" class="link">Lupa kata sandi?</a>
                                </div>

                                <button type="submit" id="btnLogin" class="btn btn-primary w-full mt-4">
                                    <span class="btn-label">Masuk</span>
                                    <span class="btn-ico" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7 17 17 7" />
                                            <path d="M9 7h8v8" />
                                        </svg>
                                    </span>
                                </button>
                            </form>

                            <p class="card-foot">
                                Belum punya akun?
                                <a href="{{ route('addKaryawanIndex') }}" class="link">Daftar</a>
                            </p>
                        </div>
                    </div>

                    <p class="legal">
                        Dengan mendaftar, Anda menyetujui
                        <b>Syarat Layanan</b> dan <b>Perjanjian Pemrosesan Data</b>.
                    </p>
                </div>
            </main>
        </div>
    </div>

    <x-session-toast />

    <script>
        const splash = document.getElementById('login-splash');
        if (!splash) {
            // Splash already shown on /.
        } else {
            const fill = splash.querySelector('.fill');
            const pct = document.getElementById('splash-pct');
            const status = document.getElementById('splash-status');
            requestAnimationFrame(() => fill.style.transform = 'scaleX(1)');
            [25, 55, 82, 100].forEach((value, index) => setTimeout(() => {
                pct.textContent = `${value}%`;
                status.textContent = ['Menghubungkan sistem', 'Memuat profil', 'Menyiapkan ruang kerja', 'Siap']
                    [index];
            }, [350, 750, 1150, 1600][index]));
            setTimeout(() => splash.classList.add('is-leaving'), 1600);
            setTimeout(() => splash.remove(), 2000);
        }
    </script>

    <script>
        (function() {
            const form = document.getElementById('loginForm');
            const button = document.getElementById('btnLogin');
            if (!form || !button) return;

            form.addEventListener('submit', function() {
                if (button.dataset.loading === '1') return;
                button.dataset.loading = '1';
                button.disabled = true;
                button.innerHTML =
                    '<span class="inline-flex items-center gap-2">' +
                    '<svg style="width:15px;height:15px;animation:spin .8s linear infinite" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                    '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"></circle>' +
                    '<path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>' +
                    '</svg> Memproses…</span>';
            });
        })();
    </script>
</body>

</html>
