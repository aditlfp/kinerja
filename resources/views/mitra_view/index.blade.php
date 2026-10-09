<x-mitra-layout title="Dashboard Mitra">
    @php
        $today = \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y');
        $hour = \Carbon\Carbon::now()->format('H');
        $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
        $jabatan = auth()->user()->divisi->jabatan->code_jabatan ?? auth()->user()->divisi->code_jabatan ?? '-';
        $menuCards = [
            [
                'route' => 'mitra_absensi',
                'title' => 'Kehadiran',
                'description' => 'Riwayat absensi tim, status hadir, dan akses cepat ke detail lokasi.',
                'icon' => 'ri-fingerprint-line',
                'icon_style' => 'color: var(--mitra-accent); background: var(--mitra-accent-soft);',
                'eyebrow' => 'Operasional harian',
            ],
            [
                'route' => 'mitra_laporan',
                'title' => 'Laporan Kerja',
                'description' => 'Pantau dokumentasi harian dan progres pekerjaan tim dalam satu daftar.',
                'icon' => 'ri-file-list-3-line',
                'icon_style' => 'color: var(--mitra-success); background: color-mix(in srgb, var(--mitra-success) 14%, transparent);',
                'eyebrow' => 'Dokumentasi kerja',
            ],
            [
                'route' => 'mitra_lembur',
                'title' => 'Data Lembur',
                'description' => 'Kelola jam lembur berjalan dan cek kebutuhan operasional tambahan.',
                'icon' => 'ri-time-line',
                'icon_style' => 'color: var(--mitra-warning); background: color-mix(in srgb, var(--mitra-warning) 16%, transparent);',
                'eyebrow' => 'Tambahan jam kerja',
            ],
            [
                'route' => 'mitra_user',
                'title' => 'Data Karyawan',
                'description' => 'Daftar personel mitra, identitas dasar, dan data yang aktif saat ini.',
                'icon' => 'ri-group-line',
                'icon_style' => 'color: #6366f1; background: rgba(99, 102, 241, 0.12);',
                'eyebrow' => 'Basis personel',
            ],
            [
                'route' => 'mitra_rekap',
                'title' => 'Rekap Bulanan',
                'description' => 'Lihat ringkasan performa dan laporan bulanan tim secara terpusat.',
                'icon' => 'ri-calendar-schedule-line',
                'icon_style' => 'color: #e11d48; background: rgba(225, 29, 72, 0.12);',
                'eyebrow' => 'Ringkasan periode',
            ],
        ];
    @endphp

    <section class="p-5 rounded-3xl mitra-panel mitra-mobile-card sm:p-6">
        <div class="flex flex-col gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] mitra-accent">Dashboard Mitra</p>
                <h1 class="mt-2 text-[1.85rem] font-extrabold leading-tight tracking-tight sm:text-[2.15rem] mitra-text-strong">
                    {{ $greeting }}, {{ auth()->user()->name }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 mitra-text-soft">
                    Akses cepat ke kehadiran, laporan kerja, lembur, dan data personel dalam satu tampilan yang lebih ringkas.
                </p>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                <div class="px-4 py-3 rounded-2xl border mitra-panel-soft mitra-text-soft">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Tanggal</p>
                    <p class="mt-2 flex items-center gap-2 text-sm font-semibold">
                        <i class="ri-calendar-line mitra-accent"></i>
                        <span>{{ $today }}</span>
                    </p>
                </div>
                <div class="px-4 py-3 rounded-2xl border mitra-panel-soft mitra-text-soft">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Posisi</p>
                    <p class="mt-2 flex items-center gap-2 text-sm font-semibold">
                        <i class="ri-shield-user-line mitra-accent"></i>
                        <span>{{ $jabatan }}</span>
                    </p>
                </div>
                <div class="px-4 py-3 rounded-2xl border mitra-panel-soft">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Status</p>
                    <p class="mt-2 flex items-center gap-2 text-sm font-semibold mitra-accent">
                        <span class="inline-flex w-2.5 h-2.5 rounded-full" style="background: var(--mitra-accent);"></span>
                        ONLINE
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.24em] mitra-accent">Statistik Inti</p>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight mitra-text-strong">Ringkasan Operasional</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 mt-5 sm:grid-cols-2">
            <article class="p-4 rounded-2xl mitra-panel-soft">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Total Karyawan</p>
                <div class="flex items-end justify-between gap-3 mt-5">
                    <p class="text-3xl font-black leading-none mitra-text-strong">{{ $jumlahKaryawan }}</p>
                    <p class="text-xs font-semibold text-right mitra-text-soft">Personel aktif</p>
                </div>
            </article>
            <article class="p-4 rounded-2xl mitra-panel-soft">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Hadir Hari Ini</p>
                <div class="flex items-end justify-between gap-3 mt-5">
                    <p class="text-3xl font-black leading-none" style="color: var(--mitra-success);">{{ $jumlahAbsensiHariIni }}</p>
                    <p class="text-xs font-semibold text-right mitra-text-soft">Absensi masuk</p>
                </div>
            </article>
            <article class="p-4 rounded-2xl mitra-panel-soft">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Lembur Berjalan</p>
                <div class="flex items-end justify-between gap-3 mt-5">
                    <p class="text-3xl font-black leading-none" style="color: var(--mitra-warning);">{{ $jumlahLemburHariIni }}</p>
                    <p class="text-xs font-semibold text-right mitra-text-soft">Jadwal aktif</p>
                </div>
            </article>
            <article class="p-4 rounded-2xl mitra-panel-soft">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">Status Sistem</p>
                <div class="flex items-end justify-between gap-3 mt-5">
                    <p class="text-2xl font-black leading-none mitra-accent">ONLINE</p>
                    <p class="text-xs font-semibold text-right mitra-text-soft">Normal</p>
                </div>
            </article>
        </div>
    </section>

    <section class="mt-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.24em] mitra-accent">Menu Utama</p>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight mitra-text-strong">Workspace Operasional</h2>
                <p class="mt-2 text-sm leading-6 mitra-text-soft">Pilih modul yang paling relevan untuk pengawasan tim dan aktivitas harian.</p>
            </div>
            <p class="hidden text-xs font-semibold uppercase tracking-[0.16em] sm:block mitra-text-muted">Lima modul aktif</p>
        </div>

        <div class="grid gap-3 mt-5 lg:grid-cols-2">
            @foreach ($menuCards as $menu)
                @continue(!Route::has($menu['route']))
                <a href="{{ route($menu['route']) }}" class="p-4 transition-all border group rounded-2xl mitra-panel-soft hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="flex items-center justify-center shrink-0 w-12 h-12 rounded-xl" style="{{ $menu['icon_style'] }}">
                                <i class="text-xl {{ $menu['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] mitra-text-muted">{{ $menu['eyebrow'] }}</p>
                                <p class="mt-1 text-lg font-bold tracking-tight mitra-text-strong">{{ $menu['title'] }}</p>
                            </div>
                        </div>
                        <i class="shrink-0 mt-1 text-lg ri-arrow-right-line mitra-text-muted"></i>
                    </div>
                    <p class="mt-4 text-sm leading-6 mitra-text-soft">{{ $menu['description'] }}</p>
                    <div class="pt-4 mt-4 border-t" style="border-color: var(--mitra-border);">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] mitra-accent">Buka modul</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</x-mitra-layout>
