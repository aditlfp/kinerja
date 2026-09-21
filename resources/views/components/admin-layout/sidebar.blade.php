@props([
    'isDashboardActive' => false,
    'activeMenu' => null,
    'isUserMenuActive' => false,
    'isDivisiMenuActive' => false,
    'isKlienMenuActive' => false,
    'isShiftMenuActive' => false,
    'isAbsensiMenuActive' => false,
    'isPoinMenuActive' => false,
    'isPerlengkapanMenuActive' => false,
    'isLaporanMenuActive' => false,
    'isCheckpointMenuActive' => false,
    'isBeritaMenuActive' => false,
    'isRekapMenuActive' => false,
    'isGajiMenuActive' => false,
])

@php
    /**
     * Satu sumber kebenaran untuk navigasi admin.
     * Urutan, label, dan URL sengaja dipertahankan identik dengan menu sebelumnya.
     */
    $menus = [
        [
            'key' => 'user',
            'label' => 'User',
            'icon' => 'manage_accounts',
            'active' => $isUserMenuActive,
            'items' => [
                ['label' => 'Data User', 'icon' => 'group', 'url' => route('admin.user.index'), 'patterns' => ['admin.user.index']],
                ['label' => 'Tambah User', 'icon' => 'person_add', 'url' => route('admin.user.create'), 'patterns' => ['admin.user.create']],
            ],
        ],
        [
            'key' => 'divisi',
            'label' => 'Divisi & Jabatan',
            'icon' => 'account_tree',
            'active' => $isDivisiMenuActive,
            'items' => [
                ['label' => 'Data Divisi', 'icon' => 'account_tree', 'url' => route('admin.divisi.index'), 'patterns' => ['admin.divisi.index']],
                ['label' => 'Tambah Divisi', 'icon' => 'domain_add', 'url' => route('admin.divisi.create'), 'patterns' => ['admin.divisi.create']],
                ['label' => 'Data Jabatan', 'icon' => 'military_tech', 'url' => route('admin.jabatan.index'), 'patterns' => ['admin.jabatan.index']],
            ],
        ],
        [
            'key' => 'klien',
            'label' => 'Klien & Lokasi',
            'icon' => 'handshake',
            'active' => $isKlienMenuActive,
            'items' => [
                ['label' => 'Data Client', 'icon' => 'business_center', 'url' => route('admin.client.index'), 'patterns' => ['admin.client.index']],
                ['label' => 'Tambah Client', 'icon' => 'domain_add', 'url' => route('admin.client.create'), 'patterns' => ['admin.client.create']],
                ['label' => 'Data Kerjasama', 'icon' => 'description', 'url' => route('admin.kerjasama.index'), 'patterns' => ['admin.kerjasama.index']],
                ['label' => 'Data Lokasi', 'icon' => 'location_on', 'url' => route('admin.lokasi.index'), 'patterns' => ['admin.lokasi.index']],
            ],
        ],
        [
            'key' => 'shift',
            'label' => 'Shift & Jadwal',
            'icon' => 'calendar_month',
            'active' => $isShiftMenuActive,
            'items' => [
                ['label' => 'Data Shift', 'icon' => 'schedule', 'url' => route('admin.shift.index'), 'patterns' => ['admin.shift.index']],
                ['label' => 'Jadwal User', 'icon' => 'event_available', 'url' => route('admin.jadwal.index'), 'patterns' => ['admin.jadwal.index']],
            ],
        ],
        [
            'key' => 'absensi',
            'label' => 'Absensi',
            'icon' => 'checklist',
            'active' => $isAbsensiMenuActive,
            'items' => [
                ['label' => 'Data Absensi', 'icon' => 'fact_check', 'url' => route('admin.absen'), 'patterns' => ['admin.absen']],
                ['label' => 'Data Izin', 'icon' => 'event_busy', 'url' => route('admin.izin.index'), 'patterns' => ['admin.izin.index']],
                ['label' => 'Data Lembur', 'icon' => 'more_time', 'url' => route('lemburList'), 'patterns' => ['lemburList']],
                ['label' => 'Data Sholat', 'icon' => 'mosque', 'url' => route('admin.report-sholat.index'), 'patterns' => ['admin.report-sholat.index']],
            ],
        ],
        [
            'key' => 'poin',
            'label' => 'Poin',
            'icon' => 'stars',
            'active' => $isPoinMenuActive,
            'items' => [
                ['label' => 'Data Poin', 'icon' => 'star', 'url' => route('admin.point.index'), 'patterns' => ['admin.point.index']],
                ['label' => 'Tambah Poin', 'icon' => 'add_circle', 'url' => route('admin.point.create'), 'patterns' => ['admin.point.create']],
            ],
        ],
        [
            'key' => 'perlengkapan',
            'label' => 'Perlengkapan',
            'icon' => 'handyman',
            'active' => $isPerlengkapanMenuActive,
            'items' => [
                ['label' => 'Data Perlengkapan', 'icon' => 'handyman', 'url' => route('admin.perlengkapan.index'), 'patterns' => ['admin.perlengkapan.index']],
                ['label' => 'Tambah Perlengkapan', 'icon' => 'add_circle', 'url' => route('admin.perlengkapan.create'), 'patterns' => ['admin.perlengkapan.create']],
            ],
        ],
        [
            'key' => 'laporan',
            'label' => 'Laporan',
            'icon' => 'assignment',
            'active' => $isLaporanMenuActive,
            'items' => [
                ['label' => 'Laporan Mitra', 'icon' => 'description', 'url' => route('laporanMitra.index'), 'patterns' => ['laporanMitra.index']],
            ],
        ],
        [
            'key' => 'checkpoint',
            'label' => 'Checkpoint',
            'icon' => 'task_alt',
            'active' => $isCheckpointMenuActive,
            'items' => [
                ['label' => 'Data Checkpoint', 'icon' => 'task_alt', 'url' => route('admin.cp.index'), 'patterns' => ['admin.cp.index']],
                ['label' => 'Data Pekerjaan CP', 'icon' => 'checklist', 'url' => route('admin.pekerjaan-cp.index'), 'patterns' => ['admin.pekerjaan-cp.index']],
            ],
        ],
        [
            'key' => 'berita',
            'label' => 'Berita',
            'icon' => 'newspaper',
            'active' => $isBeritaMenuActive,
            'items' => [
                ['label' => 'Data Berita', 'icon' => 'newspaper', 'url' => route('admin.news.index'), 'patterns' => ['admin.news.index']],
            ],
        ],
        [
            'key' => 'rekap',
            'label' => 'Rekapitulasi',
            'icon' => 'monitoring',
            'active' => $isRekapMenuActive,
            'items' => [
                [
                    'label' => 'Dashboard Rekap',
                    'icon' => 'insights',
                    'url' => route('admin.rekap.index'),
                    'patterns' => [
                        'admin.rekap.index',
                        'admin.rekap.overtimes',
                        'admin.rekap.person-out',
                        'admin.rekap.person-in',
                        'admin.rekap.cutting',
                        'admin.rekap.finished-training',
                    ],
                ],
                ['label' => 'Pengaturan Rekap', 'icon' => 'settings', 'url' => route('admin.rekap.settings'), 'patterns' => ['admin.rekap.settings']],
            ],
        ],
        [
            'key' => 'gaji',
            'label' => 'Slip Gaji',
            'icon' => 'payments',
            'active' => $isGajiMenuActive,
            'items' => [
                ['label' => 'Data Slip Gaji', 'icon' => 'receipt_long', 'url' => route('admin.slip.index'), 'patterns' => ['admin.slip.index']],
            ],
        ],
    ];

    $isActiveRoutes = fn(array $patterns): bool => request()->routeIs(...$patterns);
@endphp

<!-- NAVIGATION DRAWER (Material 3) -->
{{-- Lebar: rail (6rem) hanya berlaku di desktop; di mobile drawer selalu
     selebar penuh supaya label tetap terbaca. `data-drawer-open` dipakai CSS
     agar drawer tetap tertutup di mobile walau JavaScript belum jalan. --}}
<aside :class="{ 'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen }"
    :data-drawer-open="mobileSidebarOpen ? 'true' : null"
    class="admin-drawer fixed inset-y-0 left-0 z-50 flex w-60 flex-col overflow-hidden border-r border-[var(--md-sys-color-outline-variant)] bg-[var(--md-sys-color-surface-container-low)] lg:translate-x-0">

    {{-- Header --}}
    <div class="flex h-16 shrink-0 items-center gap-3 px-4">
        <span class="grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-xl">
            <img src="{{ asset('logo/logo_admin.png') }}" alt="Logo Kinerja App" width="36" height="36"
                class="h-9 w-9 object-contain">
        </span>
        <span x-show="sidebarOpen" x-cloak x-transition.opacity.duration.150ms
            class="truncate text-sm font-bold tracking-tight text-[var(--md-sys-color-on-surface)]">
            KINERJA APP
        </span>
        {{-- <button type="button" @click="mobileSidebarOpen = false"
            class="m3-icon-btn m3-icon-btn--sm ml-auto lg:hidden" aria-label="Tutup menu navigasi">
            <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button> --}}
    </div>

    {{-- Navigation --}}
    <nav class="custom-scrollbar flex-1 space-y-1 overflow-y-auto px-3 pb-3" aria-label="Navigasi admin">

        {{-- Dashboard --}}
        <a href="{{ route('admin.index') }}" @if ($isDashboardActive) aria-current="page" @endif
            title="Dashboard"
            class="flex items-center gap-3 rounded-full p-2 {{ $isDashboardActive
                ? 'bg-[var(--md-sys-color-secondary-container)] text-[var(--md-sys-color-on-secondary-container)]'
                : 'text-[var(--md-sys-color-on-surface-variant)]' }}">
            <md-ripple></md-ripple>
            <span class="grid h-8 w-8 shrink-0 place-items-center">
                <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
            </span>
            <span x-show="sidebarOpen" x-cloak x-transition.opacity.duration.150ms
                class="truncate text-sm font-semibold">Dashboard</span>
        </a>

        {{-- Groups --}}
        @foreach ($menus as $menu)
            @php $isOpen = $activeMenu === $menu['key']; @endphp

            <div>
                <button type="button"
                    @click="if (!sidebarOpen) { sidebarOpen = true; openMenu = '{{ $menu['key'] }}' } else { openMenu = openMenu === '{{ $menu['key'] }}' ? null : '{{ $menu['key'] }}' }"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    :aria-expanded="openMenu === '{{ $menu['key'] }}' ? 'true' : 'false'"
                    aria-controls="nav-panel-{{ $menu['key'] }}"
                    title="{{ $menu['label'] }}"
                    class="flex w-full items-center gap-3 rounded-full p-2 {{ $menu['active'] || $isOpen
                        ? 'bg-[var(--md-sys-color-secondary-container)] text-[var(--md-sys-color-on-secondary-container)]'
                        : 'text-[var(--md-sys-color-on-surface-variant)]' }}">
                    <md-ripple></md-ripple>
                    <span
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $menu['active'] || $isOpen
                            ? 'bg-[color-mix(in_srgb,var(--md-sys-color-primary)_14%,transparent)] text-[var(--md-sys-color-primary)]'
                            : 'bg-[var(--md-sys-color-surface-container-high)]' }}">
                        <span class="material-symbols-outlined" aria-hidden="true">{{ $menu['icon'] }}</span>
                    </span>
                    <span x-show="sidebarOpen" x-cloak x-transition.opacity.duration.150ms
                        class="flex min-w-0 flex-1 items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold">{{ $menu['label'] }}</span>
                        <span class="material-symbols-outlined text-[1.125rem] transition-transform duration-200"
                            :class="openMenu === '{{ $menu['key'] }}' ? 'rotate-180' : ''" aria-hidden="true">expand_more</span>
                    </span>
                </button>

                <div id="nav-panel-{{ $menu['key'] }}" x-show="openMenu === '{{ $menu['key'] }}' && sidebarOpen" x-collapse.duration.200ms
                    class="mt-1 space-y-0.5 pl-3">
                    @foreach ($menu['items'] as $item)
                        @php $itemActive = $isActiveRoutes($item['patterns']); @endphp
                        <a href="{{ $item['url'] }}" @if ($itemActive) aria-current="page" @endif
                            class="flex items-center gap-2 rounded-full py-2 pl-3 pr-2 text-xs font-medium {{ $itemActive
                                ? 'bg-[var(--md-sys-color-secondary-container)] text-[var(--md-sys-color-on-secondary-container)]'
                                : 'text-[var(--md-sys-color-on-surface-variant)] hover:text-[var(--md-sys-color-on-surface)]' }}">
                            <md-ripple></md-ripple>
                            <span class="material-symbols-outlined text-[1.125rem]" aria-hidden="true">{{ $item['icon'] }}</span>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Footer --}}
    <div class="shrink-0 border-t border-[var(--md-sys-color-outline-variant)] p-3">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" title="Logout"
                class="m3-btn m3-btn--text m3-btn--block justify-start gap-3 text-[var(--md-sys-color-error)] hover:bg-[color-mix(in_srgb,var(--md-sys-color-error)_8%,transparent)]">
                <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                <span x-show="sidebarOpen" x-cloak x-transition.opacity.duration.150ms class="text-sm">Logout</span>
            </button>
        </form>
    </div>
</aside>
