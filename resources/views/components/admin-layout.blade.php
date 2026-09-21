@props(['fullWidth' => false])
<!DOCTYPE html>
<html lang="id">

<x-admin-layout.head />

<body class="font-sans antialiased">
    <x-flasher />
    <x-session-toast />
    <x-flasher-theme />
    @php
        $isDashboardActive = request()->routeIs('admin.index');
        $isUserMenuActive = request()->routeIs('admin.user.*');
        $isDivisiMenuActive = request()->routeIs('admin.divisi.*') || request()->routeIs('admin.jabatan.*');
        $isKlienMenuActive =
            request()->routeIs('admin.client.*') || request()->routeIs('admin.kerjasama.*') || request()->routeIs('admin.lokasi.*');
        $isShiftMenuActive = request()->routeIs('admin.shift.*') || request()->routeIs('admin.jadwal.*');
        $isAbsensiMenuActive =
            request()->routeIs('admin.absen') ||
            request()->routeIs('admin.izin.*') ||
            request()->routeIs('lemburList') ||
            request()->routeIs('admin.report-sholat.*');
        $isPoinMenuActive = request()->routeIs('admin.point.*');
        $isPerlengkapanMenuActive = request()->routeIs('admin.perlengkapan.*');
        $isLaporanMenuActive =
            request()->routeIs('laporan.*') || request()->routeIs('laporanMitra.*') || request()->routeIs('qrcode.*');
        $isCheckpointMenuActive = request()->routeIs('admin.cp.*') || request()->routeIs('admin.pekerjaan-cp.*');
        $isBeritaMenuActive = request()->routeIs('admin.news.*');
        $isRekapMenuActive = request()->routeIs('admin.rekap.*');
        $isGajiMenuActive = request()->routeIs('admin.slip.index');

        $activeMenu = null;
        if ($isUserMenuActive) {
            $activeMenu = 'user';
        } elseif ($isDivisiMenuActive) {
            $activeMenu = 'divisi';
        } elseif ($isKlienMenuActive) {
            $activeMenu = 'klien';
        } elseif ($isShiftMenuActive) {
            $activeMenu = 'shift';
        } elseif ($isAbsensiMenuActive) {
            $activeMenu = 'absensi';
        } elseif ($isPoinMenuActive) {
            $activeMenu = 'poin';
        } elseif ($isPerlengkapanMenuActive) {
            $activeMenu = 'perlengkapan';
        } elseif ($isLaporanMenuActive) {
            $activeMenu = 'laporan';
        } elseif ($isCheckpointMenuActive) {
            $activeMenu = 'checkpoint';
        } elseif ($isBeritaMenuActive) {
            $activeMenu = 'berita';
        } elseif ($isRekapMenuActive) {
            $activeMenu = 'rekap';
        } elseif ($isGajiMenuActive) {
            $activeMenu = 'gaji';
        }
        // Label modul untuk top app bar, berasal dari menu yang sama dengan sidebar
        // supaya tidak ada dua daftar nama yang bisa berbeda.
        $sectionLabels = [
            'user' => 'Manajemen User',
            'divisi' => 'Divisi & Jabatan',
            'klien' => 'Klien & Lokasi',
            'shift' => 'Shift & Jadwal',
            'absensi' => 'Absensi',
            'poin' => 'Poin',
            'perlengkapan' => 'Perlengkapan',
            'laporan' => 'Laporan',
            'checkpoint' => 'Checkpoint',
            'berita' => 'Berita',
            'rekap' => 'Rekapitulasi',
            'gaji' => 'Slip Gaji',
        ];
        $sectionLabel = $isDashboardActive ? 'Dashboard' : ($sectionLabels[$activeMenu] ?? null);
    @endphp
    {{-- Permukaan datar Material 3 (surface-container-low). M3 tidak memakai gradien
         sebagai latar aplikasi: kedalaman datang dari elevasi + warna container. --}}
    <div :data-sidebar-collapsed="sidebarOpen ? null : 'true'"
        class="admin-shell m3-wash flex min-h-screen bg-[var(--md-sys-color-surface-container-low)]" x-data="{
        isDashboardActive: {{ $isDashboardActive ? 'true' : 'false' }},
        // Drawer (>=lg) dikontrol tombol toggle di topbar dan diingat antar halaman.
        // Mobile tetap memakai overlay terpisah lewat mobileSidebarOpen.
        sidebarOpen: true,
        mobileSidebarOpen: false,
        openMenu: {!! $activeMenu ? '\'' . $activeMenu . '\'' : 'null' !!},
        init() {
            const saved = localStorage.getItem('admin.sidebar');
            if (saved !== null) this.sidebarOpen = saved === 'true';
            this.$watch('sidebarOpen', value => localStorage.setItem('admin.sidebar', value));
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            // Saat rail menyempit, submenu ditutup agar tidak ada panel yatim.
            if (!this.sidebarOpen) this.openMenu = null;
        }
    }" @keydown.escape.window="mobileSidebarOpen = false">

        <div x-cloak x-show="mobileSidebarOpen" x-transition.opacity.duration.300ms @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>

        <x-admin-layout.sidebar :is-dashboard-active="$isDashboardActive" :active-menu="$activeMenu" :is-user-menu-active="$isUserMenuActive"
            :is-divisi-menu-active="$isDivisiMenuActive" :is-klien-menu-active="$isKlienMenuActive" :is-shift-menu-active="$isShiftMenuActive"
            :is-absensi-menu-active="$isAbsensiMenuActive" :is-poin-menu-active="$isPoinMenuActive"
            :is-perlengkapan-menu-active="$isPerlengkapanMenuActive" :is-laporan-menu-active="$isLaporanMenuActive"
            :is-checkpoint-menu-active="$isCheckpointMenuActive" :is-berita-menu-active="$isBeritaMenuActive"
            :is-rekap-menu-active="$isRekapMenuActive" :is-gaji-menu-active="$isGajiMenuActive" />

        <x-admin-layout.main :full-width="$fullWidth" :header-title="$headerTitle ?? $sectionLabel" :online="$online ?? null" :ip="$ip ?? null">
            {{ $slot }}
        </x-admin-layout.main>
    </div>

    <x-admin-layout.search-script />
    @stack('scripts')
</body>

</html>
