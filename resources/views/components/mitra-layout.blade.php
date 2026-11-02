@props(['title' => 'Dashboard Mitra', 'maxWidth' => 'max-w-7xl'])
@php
    $user = auth()->user();
    $initialSource = $user->nama_lengkap ?? $user->name ?? 'M';
    $userInitial = strtoupper(mb_substr(trim($initialSource), 0, 1));
    $displayName = $user->nama_lengkap ?? $user->name ?? 'Mitra';
    $workspaceName = $mitraWorkspace ?? null;
    $personelTotal = $mitraPersonelTotal ?? null;
    $izinMenunggu = $mitraIzinMenunggu ?? 0;
    $canOpenProfile = Route::has('profile.index');
    $searchValue = request()->routeIs('mitra_user') ? request('search') : null;
    $primaryMenus = [
        ['label' => 'Dashboard', 'route' => 'dashboard.index', 'icon' => 'ri-dashboard-3-line'],
        ['label' => 'Kehadiran', 'route' => 'mitra_absensi', 'icon' => 'ri-fingerprint-line'],
        ['label' => 'Karyawan', 'route' => 'mitra_user', 'icon' => 'ri-group-line'],
    ];
    $operationMenus = [
        ['label' => 'Laporan', 'route' => 'mitra_laporan', 'icon' => 'ri-file-list-3-line'],
        ['label' => 'Lembur', 'route' => 'mitra_lembur', 'icon' => 'ri-time-line'],
        ['label' => 'Izin', 'route' => 'mitra_izin', 'icon' => 'ri-article-line'],
        ['label' => 'Rekap Bulanan', 'route' => 'mitra_rekap', 'icon' => 'ri-calendar-schedule-line'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-mitra-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'SAC-PONOROGO') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            color-scheme: light;
            --mitra-bg: #f5f7fb;
            --mitra-surface: #ffffff;
            --mitra-surface-strong: #ffffff;
            --mitra-surface-soft: #f8fafc;
            --mitra-border: rgba(15, 23, 42, 0.07);
            --mitra-border-strong: rgba(15, 23, 42, 0.12);
            --mitra-text: #0f172a;
            --mitra-text-soft: #475569;
            --mitra-text-muted: #64748b;
            --mitra-accent: #2563eb;
            --mitra-accent-strong: #1d4ed8;
            --mitra-accent-soft: rgba(37, 99, 235, 0.09);
            --mitra-success: #10b981;
            --mitra-warning: #f59e0b;
            --mitra-danger: #e11d48;
            --mitra-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            --mitra-shadow-soft: 0 1px 2px rgba(15, 23, 42, 0.04);
            --mitra-sidebar-width: 18rem;
            --mitra-sidebar-collapsed: 5.5rem;
        }

        body {
            color: var(--mitra-text);
            background: var(--mitra-bg);
        }

        html,
        body {
            scrollbar-width: thin;
            scrollbar-color: rgba(100, 116, 139, 0.4) transparent;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        html::-webkit-scrollbar-track,
        body::-webkit-scrollbar-track {
            background: transparent;
        }

        html::-webkit-scrollbar-thumb,
        body::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.32);
            border: 2px solid transparent;
            background-clip: padding-box;
            border-radius: 999px;
        }

        html::-webkit-scrollbar-thumb:hover,
        body::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5);
            background-clip: padding-box;
        }

        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--mitra-accent);
            outline-offset: 2px;
        }

        [x-cloak] {
            display: none !important;
        }

        #sidebar {
            width: var(--mitra-sidebar-width);
            background: var(--mitra-surface);
            border-right: 1px solid var(--mitra-border);
            transition:
                width 0.22s ease,
                transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        #navbar {
            background: color-mix(in srgb, var(--mitra-surface) 92%, transparent);
            border-bottom: 1px solid var(--mitra-border);
            backdrop-filter: saturate(180%) blur(12px);
            -webkit-backdrop-filter: saturate(180%) blur(12px);
        }

        #sidebarOverlay {
            transition: opacity 0.22s ease;
        }

        .mitra-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--mitra-text-soft);
            transition: background-color 0.18s ease, color 0.18s ease;
        }

        .mitra-nav-link i {
            font-size: 1.125rem;
            line-height: 1;
        }

        .mitra-nav-link:hover {
            background: #f1f5f9;
            color: var(--mitra-text);
        }

        .mitra-nav-link[aria-current="page"] {
            background: var(--mitra-accent-soft);
            color: var(--mitra-accent-strong);
            font-weight: 600;
        }

        .mitra-icon-button {
            /* display: inline-flex; */
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            border: 1px solid var(--mitra-border);
            background: var(--mitra-surface);
            color: var(--mitra-text-soft);
            transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .mitra-icon-button:hover {
            background: #f1f5f9;
            color: var(--mitra-text);
            border-color: var(--mitra-border-strong);
        }

        .mitra-icon-button:active {
            transform: translateY(1px);
        }

        .mitra-panel {
            background: var(--mitra-surface);
            border: 1px solid var(--mitra-border);
            box-shadow: var(--mitra-shadow);
        }

        .mitra-panel-soft {
            background: var(--mitra-surface);
            border: 1px solid var(--mitra-border);
            box-shadow: var(--mitra-shadow-soft);
        }

        .mitra-input {
            background-color: var(--mitra-surface) !important;
            color: var(--mitra-text) !important;
            border-color: var(--mitra-border-strong) !important;
        }

        .mitra-input::placeholder {
            color: var(--mitra-text-muted);
        }

        .mitra-modal-surface {
            background: var(--mitra-surface);
            border: 1px solid var(--mitra-border);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
        }

        .mitra-info-panel {
            background: var(--mitra-surface-soft);
            color: var(--mitra-text);
            border: 1px solid var(--mitra-border);
        }

        .mitra-mobile-list-card {
            background: var(--mitra-surface);
            border: 1px solid var(--mitra-border);
            border-radius: 1rem;
        }

        .mitra-empty-state {
            color: var(--mitra-text-muted);
        }

        .mitra-section-title {
            color: var(--mitra-text-muted);
        }

        .mitra-meta-label {
            color: var(--mitra-text-muted);
        }

        .mitra-text-muted { color: var(--mitra-text-muted); }
        .mitra-text-soft { color: var(--mitra-text-soft); }
        .mitra-text-strong { color: var(--mitra-text); }
        .mitra-accent { color: var(--mitra-accent); }

        .mitra-status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.25rem 0.625rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .mitra-status-pending {
            color: #92400e;
            background: #fef3c7;
        }

        .mitra-status-success {
            color: #047857;
            background: #d1fae5;
        }

        .mitra-status-danger {
            color: #b91c1c;
            background: #fee2e2;
        }

        .mitra-link-accent {
            color: var(--mitra-accent);
            font-weight: 600;
            transition: color 0.18s ease;
        }

        .mitra-link-accent:hover {
            color: var(--mitra-accent-strong);
        }

        .mitra-avatar-fallback {
            color: var(--mitra-text-soft);
            background: #e2e8f0;
        }

        .mitra-deferred-card {
            content-visibility: auto;
            contain-intrinsic-size: 300px;
        }

        .mitra-table {
            color: var(--mitra-text-soft);
        }

        .mitra-table thead {
            color: var(--mitra-text-muted);
        }

        .mitra-table-row {
            border-color: var(--mitra-border);
        }

        .mitra-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable both-edges;
        }

        .mitra-table-wrap,
        .mitra-scrollbar,
        #sidebar nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(100, 116, 139, 0.4) transparent;
        }

        .mitra-table-wrap::-webkit-scrollbar,
        .mitra-scrollbar::-webkit-scrollbar,
        #sidebar nav::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .mitra-table-wrap::-webkit-scrollbar-track,
        .mitra-scrollbar::-webkit-scrollbar-track,
        #sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .mitra-table-wrap::-webkit-scrollbar-thumb,
        .mitra-scrollbar::-webkit-scrollbar-thumb,
        #sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.3);
            border-radius: 999px;
        }

        /* Legacy utility normalisation for the remaining mitra pages. */
        html[data-mitra-theme] [class~="bg-slate-700"],
        html[data-mitra-theme] [class~="bg-slate-800"],
        html[data-mitra-theme] [class~="bg-slate-900"],
        html[data-mitra-theme] [class~="bg-zinc-700"],
        html[data-mitra-theme] [class~="bg-zinc-800"],
        html[data-mitra-theme] [class~="bg-zinc-900"] {
            background-color: var(--mitra-surface-strong) !important;
        }

        html[data-mitra-theme] [class~="bg-slate-700/50"],
        html[data-mitra-theme] [class~="bg-slate-800/50"],
        html[data-mitra-theme] [class~="bg-slate-800/80"],
        html[data-mitra-theme] [class~="bg-slate-900/95"],
        html[data-mitra-theme] [class~="bg-slate-800/95"],
        html[data-mitra-theme] [class~="bg-zinc-900/95"] {
            background-color: var(--mitra-surface) !important;
        }

        html[data-mitra-theme] [class~="border-slate-600"],
        html[data-mitra-theme] [class~="border-slate-600/50"],
        html[data-mitra-theme] [class~="border-slate-700"],
        html[data-mitra-theme] [class~="border-slate-700/50"],
        html[data-mitra-theme] [class~="border-slate-500"],
        html[data-mitra-theme] [class~="border-slate-300"],
        html[data-mitra-theme] [class~="border-zinc-600"] {
            border-color: var(--mitra-border) !important;
        }

        html[data-mitra-theme] [class~="text-slate-100"],
        html[data-mitra-theme] [class~="text-slate-200"],
        html[data-mitra-theme] [class~="text-slate-800"] {
            color: var(--mitra-text) !important;
        }

        html[data-mitra-theme] [class~="text-slate-300"] {
            color: var(--mitra-text-soft) !important;
        }

        html[data-mitra-theme] [class~="text-slate-400"],
        html[data-mitra-theme] [class~="text-slate-500"],
        html[data-mitra-theme] [class~="text-slate-600"] {
            color: var(--mitra-text-muted) !important;
        }

        html[data-mitra-theme] [class~="bg-slate-200"] {
            background-color: #eef2f7 !important;
        }

        html[data-mitra-theme] [class*="hover:bg-slate-600"]:hover,
        html[data-mitra-theme] [class*="hover:bg-slate-800"]:hover,
        html[data-mitra-theme] [class*="hover:bg-slate-800/30"]:hover {
            background-color: #f1f5f9 !important;
        }

        html[data-mitra-theme] [class*="hover:text-white"]:hover {
            color: var(--mitra-text) !important;
        }

        html[data-mitra-theme] table thead {
            background-color: var(--mitra-surface-soft) !important;
        }

        nav[role="navigation"] {
            width: 100%;
        }

        nav[role="navigation"] > div:first-child {
            margin-bottom: 0.85rem;
        }

        nav[role="navigation"] span[aria-current="page"] span,
        nav[role="navigation"] a,
        nav[role="navigation"] span[aria-disabled="true"] span {
            border-radius: 0.75rem !important;
        }

        @media (min-width: 1024px) {
            #appContent {
                padding-left: var(--mitra-sidebar-width);
                transition: padding-left 0.22s ease;
            }

            body.sidebar-collapsed #sidebar {
                width: var(--mitra-sidebar-collapsed) !important;
            }

            body.sidebar-collapsed #appContent {
                padding-left: var(--mitra-sidebar-collapsed);
            }

            body.sidebar-collapsed #sidebar .sidebar-text,
            body.sidebar-collapsed #sidebar .sidebar-section-title,
            body.sidebar-collapsed #sidebar .sidebar-workspace {
                display: none;
            }

            body.sidebar-collapsed #sidebar .sidebar-brand,
            body.sidebar-collapsed #sidebar .mitra-nav-link {
                justify-content: center;
            }

            body.sidebar-collapsed #sidebar .sidebar-profile {
                justify-content: center;
            }

            body.sidebar-collapsed #sidebar .sidebar-profile form {
                display: none;
            }
        }

        @media (max-width: 1023px) {
            #navbar > div {
                min-height: 4.25rem;
            }

            #appContent main > div {
                padding-top: 1.5rem;
                padding-bottom: 3.5rem;
            }
        }

        @media (max-width: 768px) {
            #sidebar {
                width: min(20rem, calc(100vw - 1rem));
                margin: 0.5rem 0 0.5rem 0.5rem;
                border-radius: 1.25rem;
            }

            .mitra-mobile-table {
                min-width: 620px;
            }

            .mitra-mobile-table-wide {
                min-width: 760px;
            }
        }

        @media (max-width: 640px) {
            .mitra-mobile-card {
                padding: 1.1rem;
                border-radius: 1.25rem;
            }

            .mitra-mobile-table {
                min-width: 560px;
            }

            .mitra-mobile-table-wide {
                min-width: 700px;
            }

            #appContent main > div {
                padding-top: 1.15rem;
                padding-bottom: 4rem;
            }

            .btn,
            button,
            a.btn,
            input,
            select,
            textarea {
                min-height: 2.75rem;
            }

            nav[role="navigation"] > div:first-child {
                display: none;
            }

            nav[role="navigation"] > div:last-child {
                display: flex;
                flex-direction: column;
                gap: 0.85rem;
                align-items: stretch;
            }

            nav[role="navigation"] > div:last-child > div:last-child {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.45rem;
            }

            nav[role="navigation"] a,
            nav[role="navigation"] span[aria-disabled="true"] span,
            nav[role="navigation"] span[aria-current="page"] span {
                min-width: 2.75rem;
                min-height: 2.75rem;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                padding: 0.65rem 0.85rem !important;
                font-size: 0.82rem !important;
            }
        }
    </style>
    {{-- Dropdown kustom untuk semua <select> native (progressive enhancement). --}}
    <link rel="stylesheet" href="{{ URL::asset('css/nselect.css') }}?v={{ filemtime(public_path('css/nselect.css')) }}">
    <script defer src="{{ URL::asset('js/nselect.js') }}?v={{ filemtime(public_path('js/nselect.js')) }}"></script>
</head>
<body id="mainBody" class="font-sans antialiased">
    <x-flasher />
    <x-session-toast />
    <x-flasher-theme />

    <div class="relative min-h-screen lg:flex">
        <aside id="sidebar" class="fixed inset-y-0 -left-10 sm:left-0 z-50 flex flex-col max-h-screen p-4 -translate-x-full shadow-sm lg:translate-x-0">
            <div class="flex items-center gap-3 px-1 py-1 sidebar-brand">
                <div class="flex items-center justify-center text-white rounded-xl w-9 h-9" style="background: var(--mitra-accent);">
                    <i class="text-lg ri-leaf-line"></i>
                </div>
                <div class="min-w-0 sidebar-text">
                    <p class="text-sm font-bold leading-tight mitra-text-strong">Portal Mitra</p>
                    <p class="text-xs leading-tight mitra-text-muted">Dashboard operasional</p>
                </div>
            </div>

            <nav class="flex-1 mt-6 overflow-y-auto">
                <p class="sidebar-section-title px-2 mb-2 text-[10px] font-bold uppercase tracking-[0.14em] mitra-text-muted">Menu Utama</p>
                <div class="space-y-1">
                    @foreach($primaryMenus as $menu)
                        @continue(! Route::has($menu['route']))
                        @php $isActive = request()->routeIs($menu['route']) || request()->routeIs($menu['route'] . '.*'); @endphp
                        <a href="{{ route($menu['route']) }}"
                           class="mitra-nav-link"
                           title="{{ $menu['label'] }}"
                           @if($isActive) aria-current="page" @endif>
                            <i class="{{ $menu['icon'] }}"></i>
                            <span class="sidebar-text">{{ $menu['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <p class="sidebar-section-title px-2 mt-5 mb-2 text-[10px] font-bold uppercase tracking-[0.14em] mitra-text-muted">Operasional</p>
                <div class="space-y-1">
                    @foreach($operationMenus as $menu)
                        @continue(! Route::has($menu['route']))
                        @php $isActive = request()->routeIs($menu['route']) || request()->routeIs($menu['route'] . '.*'); @endphp
                        <a href="{{ route($menu['route']) }}"
                           class="mitra-nav-link"
                           title="{{ $menu['label'] }}"
                           @if($isActive) aria-current="page" @endif>
                            <i class="{{ $menu['icon'] }}"></i>
                            <span class="sidebar-text">{{ $menu['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                @if($canOpenProfile)
                    <p class="sidebar-section-title px-2 mt-5 mb-2 text-[10px] font-bold uppercase tracking-[0.14em] mitra-text-muted">Akun</p>
                    <a href="{{ route('profile.index') }}" class="mitra-nav-link" title="Pengaturan Akun" @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                        <i class="ri-settings-3-line"></i>
                        <span class="sidebar-text">Pengaturan Akun</span>
                    </a>
                @endif
            </nav>

            <div class="pt-4 mt-4 border-t space-y-3" style="border-color: var(--mitra-border);">
                <div class="sidebar-workspace px-3 py-3 rounded-xl border" style="border-color: var(--mitra-border); background: var(--mitra-surface-soft);">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] mitra-text-muted">Workspace</p>
                    <p class="mt-1 text-sm font-semibold leading-tight truncate mitra-text-strong">{{ $workspaceName ?: 'Mitra belum terhubung' }}</p>
                    <p class="mt-0.5 text-xs mitra-text-muted">
                        @if(! is_null($personelTotal))
                            {{ $personelTotal }} personel terdaftar
                        @else
                            Akun mitra
                        @endif
                    </p>
                </div>

                <div class="sidebar-profile flex items-center gap-3 px-1">
                    <span class="inline-flex items-center justify-center flex-shrink-0 text-sm font-bold rounded-full w-9 h-9 mitra-avatar-fallback">
                        {{ $userInitial }}
                    </span>
                    <div class="min-w-0 sidebar-text">
                        <p class="text-sm font-semibold leading-tight truncate mitra-text-strong">{{ $displayName }}</p>
                        <p class="text-xs leading-tight truncate mitra-text-muted">{{ $user->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0 ml-auto">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 transition rounded-lg hover:bg-slate-100 focus-visible:outline-none" title="Keluar" aria-label="Keluar dari akun">
                            <i class="text-base ri-logout-box-r-line mitra-text-muted"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden opacity-0 bg-slate-950/40 lg:hidden"></div>

        <div id="appContent" class="flex flex-col flex-1 min-h-[100dvh]">
            <nav id="navbar" class="sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 gap-3 px-4 lg:px-8">
                    <div class="flex items-center min-w-0 gap-3">
                        <button id="sidebarToggle" type="button" class="mitra-icon-button sm:hidden" aria-label="Buka menu navigasi">
                            <i class="text-lg ri-menu-line"></i>
                        </button>
                        <button id="sidebarCollapseToggle" type="button" class="hidden mitra-icon-button sm:inline-flex" title="Lipat sidebar" aria-label="Lipat sidebar">
                            <i id="sidebarCollapseIcon" class="text-lg ri-layout-left-2-line"></i>
                        </button>
                        <h1 class="text-base font-semibold tracking-tight truncate mitra-text-strong">{{ $title }}</h1>
                    </div>

                    <div class="flex items-center flex-shrink-0 gap-2">
                        <form method="GET" action="{{ route('mitra_user') }}" role="search" class="relative hidden md:block">
                            <label for="mitraSearch" class="sr-only">Cari karyawan</label>
                            <i class="absolute text-base pointer-events-none ri-search-line left-3 top-1/2 -translate-y-1/2 mitra-text-muted"></i>
                            <input
                                id="mitraSearch"
                                type="search"
                                name="search"
                                value="{{ $searchValue }}"
                                placeholder="Cari nama karyawan..."
                                autocomplete="off"
                                class="w-56 py-2 pl-9 pr-3 text-sm transition border outline-none rounded-xl lg:w-64 mitra-input focus:border-[color:var(--mitra-accent)]"
                            >
                        </form>

                        <a href="{{ route('mitra_izin') }}"
                           class="relative mitra-icon-button"
                           title="Pengajuan izin menunggu persetujuan"
                           aria-label="Notifikasi, {{ $izinMenunggu }} pengajuan izin menunggu persetujuan">
                            <i class="text-lg ri-notification-3-line"></i>
                            @if($izinMenunggu > 0)
                                <span class="absolute inline-flex items-center justify-center min-w-[1.15rem] h-[1.15rem] px-1 text-[10px] font-bold text-white rounded-full -top-1 -right-1" style="background: var(--mitra-danger);">
                                    {{ $izinMenunggu > 99 ? '99+' : $izinMenunggu }}
                                </span>
                            @endif
                        </a>

                        @if($canOpenProfile)
                            <a href="{{ route('profile.index') }}" class="inline-flex items-center gap-2 px-1 py-1 transition rounded-full hover:bg-slate-100" title="Buka profil">
                                <span class="inline-flex items-center justify-center text-sm font-bold rounded-full w-9 h-9 mitra-avatar-fallback">{{ $userInitial }}</span>
                                <span class="hidden text-sm font-semibold lg:inline mitra-text-strong">{{ $displayName }}</span>
                            </a>
                        @else
                            <span class="inline-flex items-center gap-2 px-1 py-1">
                                <span class="inline-flex items-center justify-center text-sm font-bold rounded-full w-9 h-9 mitra-avatar-fallback">{{ $userInitial }}</span>
                                <span class="hidden text-sm font-semibold lg:inline mitra-text-strong">{{ $displayName }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </nav>

            <main class="flex-1">
                <div class="{{ $maxWidth }} px-4 py-8 mx-auto lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapseToggle = document.getElementById('sidebarCollapseToggle');
        const sidebarCollapseIcon = document.getElementById('sidebarCollapseIcon');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            if (!sidebar || !sidebarOverlay) return;
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.remove('-left-10');
            sidebar.classList.add('left-0');
            sidebarOverlay.classList.remove('hidden');
            document.body.style.overflowY = 'hidden';
            document.body.style.overflowX = 'hidden';
            requestAnimationFrame(function () {
                sidebarOverlay.classList.remove('opacity-0');
                sidebarOverlay.classList.add('opacity-100');
            });
        }

        function closeSidebar() {
            if (!sidebar || !sidebarOverlay) return;
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('left-0');
            sidebar.classList.add('-left-10');
            sidebarOverlay.classList.remove('opacity-100');
            sidebarOverlay.classList.add('opacity-0');
            document.body.style.overflowY = '';
            document.body.style.overflowX = '';
            window.setTimeout(function () {
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebarOverlay.classList.add('hidden');
                }
            }, 220);
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', openSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        function setSidebarCollapsed(collapsed) {
            document.body.classList.toggle('sidebar-collapsed', collapsed);

            if (sidebarCollapseIcon) {
                sidebarCollapseIcon.className = collapsed
                    ? 'text-lg ri-layout-right-2-line'
                    : 'text-lg ri-layout-left-2-line';
            }

            if (sidebarCollapseToggle) {
                sidebarCollapseToggle.setAttribute('aria-label', collapsed ? 'Bentangkan sidebar' : 'Lipat sidebar');
                sidebarCollapseToggle.setAttribute('title', collapsed ? 'Bentangkan sidebar' : 'Lipat sidebar');
            }

            try {
                localStorage.setItem('mitra_sidebar_collapsed', collapsed ? '1' : '0');
            } catch (error) {}
        }

        if (window.innerWidth >= 1024) {
            try {
                setSidebarCollapsed(localStorage.getItem('mitra_sidebar_collapsed') === '1');
            } catch (error) {}
        }

        if (sidebarCollapseToggle) {
            sidebarCollapseToggle.addEventListener('click', function () {
                setSidebarCollapsed(!document.body.classList.contains('sidebar-collapsed'));
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });
    </script>
</body>
</html>
