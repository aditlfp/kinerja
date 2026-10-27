<!DOCTYPE html>
<html lang="id" data-theme="bumblebee">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APP_NAME', 'Kinerja SAC-PONOROGO') }}</title>
    <link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="{{ URL::asset('src/js/jquery-min.js') }}"></script>
    {{-- <script src="{{ URL::asset('src/js/serviceWorker.min.js') }}"></script> --}}

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($shouldTrackPulang)
        {{-- Leaflet --}}
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endif


    @include('dashboard.partials.styles')

</head>

<body class="bg-slate-50 font-sans antialiased text-slate-800 max-w-full">
    @php
        $user = Auth::user();
        $jabatan = $user?->divisi?->jabatan?->code_jabatan;
        $directJabatan = $user?->jabatan?->code_jabatan;
        $roleId = $user?->role_id;
        $kerjasamaId = $user?->kerjasama_id;
    @endphp

    @php
        /*
        |--------------------------------------------------------------------------
        | USER CONTEXT
        |--------------------------------------------------------------------------
        */
        $userId = $user->id;
        $jabatan = $user->jabatan;
        $jabatanId = $user->jabatan_id;
        $roleId = $roleId;
        $kerjasamaId = $kerjasamaId;
        $devisiId = $user->devisi_id;
        $directJabatan = $directJabatan;

        /*
        |--------------------------------------------------------------------------
        | ACCESS / ROLE
        |--------------------------------------------------------------------------
        */
        $isMitra = $jabatan->code_jabatan == 'MITRA';
        $isDireksi = $jabatanId == 24;

        $isLeader = $jabatan->code_jabatan == 'LEADER';
        $isCoCs = $jabatan->code_jabatan == 'CO-CS';
        $isCoScr = $jabatan->code_jabatan == 'CO-SCR';
        $isSPVW = $jabatan->code_jabatan == 'SPV-W';

        $isManajemen = $kerjasamaId == 1;
        $isAdmin = $roleId == 2;

        $isSPV = $user->jabatan_id == 4 || $user->jabatan_id == 14;

        /*
        |--------------------------------------------------------------------------
        | ABSENSI
        |--------------------------------------------------------------------------
        */
        $hariIni = now()->dayOfWeekIso;

        $isWeekend = in_array($hariIni, [6, 7]);

        $tampilkanAbsensi = !$isWeekend || !$isManajemen || $devisiId != 26;

        /*
        |--------------------------------------------------------------------------
        | ABSENSI ROUTE
        |--------------------------------------------------------------------------
        */
        $absensiRoute = match ($jabatan->code_jabatan) {
            'CO-CS' => 'absensi-karyawan-co-cs.index',
            'CO-SCR' => 'absensi-karyawan-co-scr.index',
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | REKAP
        |--------------------------------------------------------------------------
        */
        $jabatanRekap = ['CO-CS', 'CO-SCR', 'SPV-W'];

        $bisaLihatRekap = in_array($jabatan->code_jabatan, $jabatanRekap) && $userId != 7;

        $rekapRoute = $isSPVW ? 'spvw.rekap.index' : 'index.rekap.data.leader';

        /*
        |--------------------------------------------------------------------------
        | KONTRAK
        |--------------------------------------------------------------------------
        */
        $kontrakSelesai = $kontrak?->tgl_selesai_kontrak
            ? \Carbon\Carbon::parse($kontrak->tgl_selesai_kontrak)->isPast()
            : false;

        $kontrakBerjalan = $kontrak && ($kontrak->isPending() || $kontrak->isActive() || $kontrak->isProses());

        $bisaAjukanKontrak = !$kontrak || $kontrakSelesai || !$kontrakBerjalan;

        /*
        |--------------------------------------------------------------------------
        | MENU KHUSUS
        |--------------------------------------------------------------------------
        */
        $bisaLihatMenuManajemen = in_array($jabatanId, [4, 14]);

        $bisaLihatMenuSlip = in_array($userId, [7, 10, 5]);

        /*
        |--------------------------------------------------------------------------
        | MENU ROLE
        |--------------------------------------------------------------------------
        */
        $routes = [
            'MITRA' => [
                'karyawan' => 'mitra_user',
                'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'mitra_jadwal',
                'absensi' => 'mitra_absensi',
                'izin' => 'mitra_izin',
                'lembur' => 'mitra_lembur',
                'laporan' => 'mitra_laporan',
                'rating' => 'mitra-rating.index',
                'laporan bulanan' => 'mitra-laporan-bulanan.index',
            ],

            'LEADER' => [
                'karyawan' => 'lead_user',
                'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'leader-jadwal.index',
                'absensi' => 'lead_absensi',
                'izin' => 'lead_izin',
                'lembur' => 'lead_lembur',
                'laporan' => 'lead_laporan',
                'rating' => 'leader-rating.index',
            ],

            'DIREKSI' => [
                'karyawan' => 'direksi_user',
                'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'direksi_jadwal',
                'absensi' => 'direksi_absensi',
                'izin' => 'direksi_izin',
                'lembur' => 'direksi_lembur',
                'laporan' => 'direksi_laporan',
                'rating' => 'direksi-rating.index',
                'kinerja' => 'direksi.cp.index',
                'kontrak' => 'direksi-cekKontrak',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | ICON MENU
        |--------------------------------------------------------------------------
        */
        $icons = [
            'karyawan' => 'ri-pass-pending-line',
            'jadwal' => 'ri-calendar-check-line',
            'absensi' => 'ri-todo-line',
            'izin' => 'ri-shield-user-line',
            'lembur' => 'ri-time-line',
            'laporan' => 'ri-image-add-line',
            'laporan bulanan' => 'ri-image-add-line',
            'rating' => 'ri-sparkling-line',
            'kinerja' => 'ri-sparkling-line',
            'kontrak' => 'ri-pass-pending-line',
        ];
    @endphp
    @php
        /*
        |--------------------------------------------------------------------------
        | DASHBOARD MENU UTAMA
        |--------------------------------------------------------------------------
        */

        $dashboardMenus = [
            // base menu
            [
                'key' => 'kehadiran',
                'label' => 'Kehadiran',
                'route' => 'absensi.index',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
            ],

            [
                'key' => 'data-rekap',
                'label' => 'Data Rekap',
                'route' => $rekapRoute,
                'icon' => 'ri-database-line',
                'color' => 'indigo',
                'show' => $bisaLihatRekap,
            ],

            [
                'key' => 'izin',
                'label' => 'Izin',
                'route' => 'izin.create',
                'icon' => 'ri-file-line',
                'color' => 'blue',
            ],

            [
                'key' => 'kinerja',
                'label' => 'Kinerja',
                'route' => 'checkpoint-user.index',
                'icon' => 'ri-bar-chart-line',
                'color' => 'amber',
                'show' => !$isDireksi || $isManajemen, // Hide for Direksi
            ],

            [
                'key' => 'rating',
                'label' => 'Rating',
                'route' => 'ratingSaya',
                'params' => [$user->id],
                'icon' => 'ri-star-line',
                'color' => 'indigo',
                'show' => !$isDireksi, // Hide for Direksi
            ],

            [
                'key' => 'laporan',
                'label' => 'Laporan',
                'route' => 'laporan.index',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
                'show' => !$isDireksi, // Hide for Direksi
            ],

            [
                'key' => 'kontrak',
                'label' => 'Pengajuan Kontrak',
                'route' => 'form-kontrak-request',
                'icon' => 'ri-file-add-line',
                'color' => 'green',
                'show' => $bisaAjukanKontrak && !$isDireksi, // Show if user can submit contract or is not Direksi
            ],

            // addons
            [
                'key' => 'alquran',
                'label' => "Al Qur'an",
                'url' => 'https://baca-alquran.sac-po.com',
                'icon' => 'ri-book-line',
                'color' => 'amber',
            ],

            [
                'key' => 'sppd',
                'label' => 'SPPD',
                'url' => 'https://sppd-online.sac-po.com/login',
                'icon' => 'ri-road-map-line',
                'color' => 'sky',
                'show' => $isManajemen,
            ],
        ];

        // menu tambahan sesuai role
        $dashboardAddMenus = [
            // direksi menu
            [
                'key' => 'karyawan-direksi',
                'label' => 'Karyawan',
                'route' => 'direksi_user',
                'icon' => 'ri-team-line',
                'color' => 'blue',
                'show' => $isDireksi,
            ],

            [
                'key' => 'absensi-direksi',
                'label' => 'Absensi',
                'route' => 'direksi_absensi',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isDireksi,
            ],

            [
                'key' => 'izin-direksi',
                'label' => 'Izin',
                'route' => 'direksi_izin',
                'icon' => 'ri-shield-user-line',
                'color' => 'blue',
                'show' => $isDireksi,
            ],

            [
                'key' => 'kinerja-direksi',
                'label' => 'Kinerja',
                'route' => 'direksi.cp.index',
                'icon' => 'ri-bar-chart-line',
                'color' => 'amber',
                'show' => $isDireksi,
            ],

            [
                'key' => 'kontrak-direksi',
                'label' => 'Kontrak',
                'route' => 'direksi-cekKontrak',
                'icon' => 'ri-file-add-line',
                'color' => 'green',
                'show' => $isDireksi,
            ],

            // SPV menu
            [
                'key' => 'data-rekap',
                'label' => 'Data Rekap',
                'route' => 'manajemen_rekap',
                'icon' => 'ri-database-line',
                'color' => 'purple',
                'show' => $isSPV,
            ],

            [
                'key' => 'slip-gaji-karyawan',
                'label' => 'Slip Gaji Karyawan',
                'route' => 'slip-karyawan',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
                'show' => $isSPV,
            ],

            [
                'key' => 'absensi-karyawan',
                'label' => 'Absensi Karyawan',
                'route' => 'manajemen_absensi',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isSPV,
            ],

            [
                'key' => 'laporan-karyawan',
                'label' => 'Laporan Karyawan',
                'route' => 'manajemen_laporan',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
                'show' => $isSPV,
            ],

            [
                'key' => 'data-karyawan',
                'label' => 'Data Karyawan',
                'route' => 'manajemen_user',
                'icon' => 'ri-user-line',
                'color' => 'green',
                'show' => $isSPV,
            ],

            // menu SPV-W
            [
                'key' => 'data-karyawan-spvw',
                'label' => 'Data Karyawan',
                'route' => 'spvw_user',
                'icon' => 'ri-user-line',
                'color' => 'green',
                'show' => $isSPVW,
            ],

            [
                'key' => 'data-absensi-spvw',
                'label' => 'Data Absensi',
                'route' => 'spvw_absensi',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isSPVW,
            ],

            [
                'key' => 'data-izin-spvw',
                'label' => 'Data Izin',
                'route' => 'spvw_izin',
                'icon' => 'ri-file-line',
                'color' => 'blue',
                'show' => $isSPVW,
            ],

            [
                'key' => 'data-rating-spvw',
                'label' => 'Data Rating',
                'route' => 'spvw-rating.index',
                'icon' => 'ri-star-line',
                'color' => 'yellow',
                'show' => $isSPVW,
            ],

            [
                'key' => 'data-checklist-spvw',
                'label' => 'Data Checklist',
                'route' => 'spvw-checklist.index',
                'icon' => 'ri-list-check',
                'color' => 'indigo',
                'show' => $isSPVW,
            ],

            [
                'key' => 'data-slip-spvw',
                'label' => 'Slip Gaji Karyawan',
                'route' => 'spvw-slip',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
                'show' => $isSPVW,
            ],

            // menu co-cs
            [
                'key' => 'data-karyawan-leader',
                'label' => 'Data Karyawan',
                'route' => 'lead_user',
                'icon' => 'ri-user-line',
                'color' => 'green',
                'show' => $isCoCs,
            ],

            [
                'key' => 'data-absensi-leader',
                'label' => 'Data Absensi',
                'route' => 'lead_absensi',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isCoCs,
            ],

            [
                'key' => 'data-izin-leader',
                'label' => 'Data Izin',
                'route' => 'lead_izin',
                'icon' => 'ri-file-line',
                'color' => 'blue',
                'show' => $isCoCs,
            ],

            [
                'key' => 'data-absen-sholat-leader',
                'label' => 'Data Sholat',
                'route' => 'leader-absenSholat',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isCoCs,
            ],

            [
                'key' => 'data-rating-leader',
                'label' => 'Data Rating',
                'route' => 'leader-rating.index',
                'icon' => 'ri-star-line',
                'color' => 'yellow',
                'show' => $isCoCs,
            ],

            [
                'key' => 'data-checklist-leader',
                'label' => 'Data Checklist',
                'route' => 'leader-checklist.index',
                'icon' => 'ri-list-check',
                'color' => 'indigo',
                'show' => $isCoCs,
            ],

            [
                'key' => 'data-slip-leader',
                'label' => 'Slip Gaji Karyawan',
                'route' => 'leader-slip',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
                'show' => $isCoCs,
            ],

            // menu co-scr
            [
                'key' => 'data-karyawan-leader',
                'label' => 'Data Karyawan',
                'route' => 'danru_user',
                'icon' => 'ri-user-line',
                'color' => 'green',
                'show' => $isCoScr,
            ],

            [
                'key' => 'data-absensi-leader',
                'label' => 'Data Absensi',
                'route' => 'danru_absensi',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isCoScr,
            ],

            [
                'key' => 'data-izin-leader',
                'label' => 'Data Izin',
                'route' => 'danru_izin',
                'icon' => 'ri-file-line',
                'color' => 'blue',
                'show' => $isCoScr,
            ],

            [
                'key' => 'data-absen-sholat-leader',
                'label' => 'Data Absen Sholat',
                'route' => 'leader-absenSholat',
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
                'show' => $isCoScr,
            ],

            [
                'key' => 'data-rating-leader',
                'label' => 'Data Rating',
                'route' => 'danru-rating.index',
                'icon' => 'ri-star-line',
                'color' => 'yellow',
                'show' => $isCoScr,
            ],

            [
                'key' => 'data-checklist-leader',
                'label' => 'Data Checklist',
                'route' => 'leader-checklist.index',
                'icon' => 'ri-list-check',
                'color' => 'indigo',
                'show' => $isCoScr,
            ],

            [
                'key' => 'data-slip-leader',
                'label' => 'Slip Gaji Karyawan',
                'route' => 'danru-slip',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
                'show' => $isCoScr,
            ],
        ];

        // history menu
        $historyMenus = [
            [
                'label' => 'Kehadiran',
                'url' => url('historyAbsensi'),
                'icon' => 'ri-calendar-line',
                'color' => 'emerald',
            ],
            [
                'label' => 'Izin',
                'url' => route('izin.index'),
                'icon' => 'ri-file-line',
                'color' => 'blue',
            ],
            [
                'label' => 'Kinerja',
                'url' => route('checkpoint-user.history'),
                'icon' => 'ri-bar-chart-line',
                'color' => 'amber',
            ],
            [
                'label' => 'Laporan',
                'url' => 'https://laporan-sac.sac-po.com',
                'icon' => 'ri-file-text-line',
                'color' => 'sky',
            ],
        ];

        // Hanya tampilkan menu yang memenuhi kondisi
        $dashboardMenus = collect($dashboardMenus)->filter(fn($menu) => $menu['show'] ?? true)->values();

        $dashboardAddMenus = collect($dashboardAddMenus)->filter(fn($menu) => $menu['show'] ?? true)->values();
    @endphp
    <div class="min-h-screen pb-24 relative">
        {{-- @include('../layouts/navbar') --}}

        {{-- navbar --}}
        <div class="pt-5 pb-3 px-6 space-y-4">
            <div class="flex items-center justify-between">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" srcset="" class="w-32 h-auto">
                <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('slip-gaji.index', ['bulan' => now()->format('Y-m')]) }}"
                        class="flex items-center gap-2 bg-amber-500/10 border border-amber-500/15 py-1 px-3 rounded-2xl">
                        <i class="ri-wallet-3-line text-xl"></i>
                        <span class="font-medium">Slip</span>
                    </a>
                    <form action="{{ route('logout') }}" method="post"
                        class="min-w-0 py-1 px-3 border border-red-500/15 bg-red-100 rounded-xl">
                        @csrf
                        @method('POST')
                        <button type="submit"
                            class="flex w-full flex-col items-center gap-1 rounded-lg">
                            <i class="ri-logout-box-r-line text-lg text-red-500"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div>
                <a href="{{ route('profile.index') }}" class="flex items-center justify-start font-extrabold text-lg gap-2 max-w-[55svw]">
                    <i class="ri-user-line text-lg"></i>
                    <span class="truncate">
                        Halo, {{ ucwords(strtolower($user->nama_lengkap)) }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Status Card --}}
        <div class="flex w-full justify-center px-4 py-2">
            @if (!$absenP)
                <span
                    class="inline-flex items-center gap-2 rounded-lg bg-rose-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-xs">
                    <i class="ri-information-line text-lg"></i>
                    <span>Kamu belum melakukan presensi hari ini</span>
                </span>
            @elseif ($absenP->absensi_type_pulang == null)
                <span
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-xs">
                    <i class="ri-time-line text-lg"></i>
                    <span>Kamu sedang dalam shift</span>
                </span>
            @else
                <span
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-xs">
                    <i class="ri-check-double-line text-lg"></i>
                    <span>Kamu sudah melakukan presensi hari ini</span>
                </span>
            @endif
        </div>

        {{-- Hero --}}
        <div class="px-6 py-2 space-y-2">

            {{-- Title --}}
            <div class="flex items-center gap-2">
                <span class="w-1 h-5 rounded-full bg-sky-500"></span>
                <span class="font-black text-lg">
                    Informasi Singkat
                </span>
            </div>

            {{-- Cards --}}
            <div class="flex items-stretch gap-1">

                {{-- =========================
                    CARD TANGGAL & JAM
                ========================== --}}
                <div
                    class="relative overflow-hidden
                        bg-emerald-500
                        min-h-[130px] w-[45%]
                        rounded-xl p-3
                        text-white
                        flex flex-col justify-between">

                    {{-- Dekorasi lingkaran --}}
                    <div
                        class="absolute -right-10 -bottom-10
                            w-28 h-28
                            rounded-full
                            bg-white/10">
                    </div>

                    {{-- Tombol / Arrow --}}
                    <div
                        class="absolute top-3 right-3
                            w-7 h-7
                            rounded-full
                            bg-white/10
                            flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    {{-- Tanggal --}}
                    <div class="relative z-10 flex items-center gap-2">

                        {{-- Calendar --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="17" rx="2" />
                            <path stroke-linecap="round" d="M16 2v4M8 2v4M3 9h18" />
                        </svg>

                        <div class="leading-tight">
                            <span class="block text-[11px] font-medium">
                                {{ now()->translatedFormat('l') }}
                            </span>

                            <span class="block text-[10px] opacity-90">
                                {{ now()->translatedFormat('d M Y') }}
                            </span>
                        </div>

                    </div>


                    {{-- Jam --}}
                    <div class="relative z-10">

                        <span id="heroClock"
                            class="block text-[30px] leading-none
                                font-black tracking-tight">
                            --:--
                        </span>

                        <div class="flex items-center gap-1 mt-1">
                            <i class="fas fa-globe"></i>
                            <span id="heroTimezone" class="block text-[9px] opacity-90 font-medium">
                                ---
                            </span>

                        </div>

                    </div>

                </div>


                {{-- =========================
                    CARD MOTIVASI
                ========================== --}}
                <div
                    class="relative overflow-hidden
                        bg-amber-400
                        min-h-[130px] w-[55%]
                        rounded-xl p-3
                        text-slate-800
                        flex flex-col justify-between">

                    {{-- Dekorasi bawah --}}
                    <div
                        class="absolute
                            -right-8 -bottom-12
                            w-32 h-32
                            rounded-full
                            bg-white/10">
                    </div>

                    {{-- Icon matahari --}}
                    <div
                        class="absolute top-3 right-3
                            w-7 h-7
                            rounded-full
                            bg-white/40
                            flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3" />

                            <path stroke-linecap="round" d="M12 2v2
                                M12 20v2
                                M4.93 4.93l1.41 1.41
                                M17.66 17.66l1.41 1.41
                                M2 12h2
                                M20 12h2
                                M4.93 19.07l1.41-1.41
                                M17.66 6.34l1.41-1.41" />
                        </svg>
                    </div>


                    {{-- Text --}}
                    <div class="relative z-10 pr-7">

                        <span
                            class="block
                                text-[15px]
                                leading-[1.05]
                                font-black">
                            Ayo tingkatkan
                            kinerjamu hari ini!
                        </span>

                        <span
                            class="block
                                mt-1
                                text-[9px]
                                leading-tight
                                font-medium
                                opacity-80">
                            Disiplin, konsisten, hasil lebih baik.
                        </span>

                    </div>


                    {{-- Grafik --}}
                    <div class="relative z-10
                            self-end
                            mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 opacity-60" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            {{-- Bar --}}
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19v-4
                                M9 19v-7
                                M14 19v-10" />

                            {{-- Grafik naik --}}
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 14l5-4 4 2 7-7" />

                            {{-- Arrow --}}
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5h4v4" />
                        </svg>
                    </div>

                </div>

            </div>
        </div>




        {{-- toggle --}}
        <div id="mainToggle"
            class="relative flex items-center justify-evenly
                text-base gap-2 py-2 my-5 mx-5
                rounded-full bg-white overflow-hidden">
            {{-- Sliding background --}}
            <div id="toggleIndicator"
                class="absolute h-[40px] w-[100px]
                    rounded-3xl bg-sky-500
                    transition-all duration-300 ease-in-out">
            </div>

            {{-- Info --}}
            <button type="button" data-index="0"
                class="toggle-btn relative z-10
                    min-h-[40px] w-[100px]
                    transition-all duration-300">
                Info
            </button>

            {{-- Utama --}}
            <button type="button" data-index="1"
                class="toggle-btn relative z-10
                    min-h-[40px] w-[100px]
                    font-extrabold text-white
                    transition-all duration-300">
                Utama
            </button>

            {{-- Riwayat --}}
            <button type="button" data-index="2"
                class="toggle-btn relative z-10
                    min-h-[40px] w-[100px]
                    transition-all duration-300">
                Riwayat
            </button>
        </div>

        <div id="dashboardPanels" class="overflow-hidden">
            <div id="dashboardPanelTrack" class="flex w-[300%] transition-transform duration-300 ease-in-out">
                {{-- menu informasi --}}
                <section class="w-1/3 shrink-0">
                    @if (auth()->user()->kerjasama_id == 1)
                        <div class="px-6 py-2">
                            <x-notification-dropdown />
                        </div>
                    @endif
                </section>

                {{-- menu utama --}}
                <section class="w-1/3 shrink-0">
                    @if ($dashboardMenus->isNotEmpty())
                        <div class="px-6 py-2 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-1 h-5 rounded-full bg-sky-500"></span>
                                <span class="font-black text-lg">Menu Utama</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1 items-start text-center">
                                @foreach ($dashboardMenus as $menu)
                                    <a href="{{ isset($menu['url']) ? $menu['url'] : route($menu['route'], $menu['params'] ?? []) }}"
                                        class="flex flex-col items-center justify-center
                                        gap-1 rounded-lg px-3 py-2">

                                        <i
                                            class="{{ $menu['icon'] }} text-3xl font-bold
                                            text-{{ $menu['color'] }}-700
                                            bg-{{ $menu['color'] }}-200
                                            py-3 px-4 rounded-2xl"></i>

                                        <span class="font-extrabold text-sm">
                                            {{ $menu['label'] }}
                                        </span>

                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- menu tambahan --}}
                    @if ($dashboardAddMenus->isNotEmpty())
                        <div class="px-6 py-2 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-1 h-5 rounded-full bg-sky-500"></span>
                                <span class="font-black text-lg">Menu Tambahan</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1 items-start text-center">
                                @foreach ($dashboardAddMenus as $menu)
                                    <a href="{{ isset($menu['url']) ? $menu['url'] : route($menu['route'], $menu['params'] ?? []) }}"
                                        class="flex flex-col items-center justify-center
                                        gap-1 rounded-lg px-3 py-2">

                                        <i
                                            class="{{ $menu['icon'] }}
                                            text-3xl font-bold
                                            text-{{ $menu['color'] }}-700
                                            bg-{{ $menu['color'] }}-300
                                            py-3 px-4 rounded-2xl"></i>

                                        <span class="font-extrabold text-sm">
                                            {{ $menu['label'] }}
                                        </span>

                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </section>

                {{-- menu riwayat --}}
                <section class="w-1/3 shrink-0 px-6 py-2 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-1 h-5 rounded-full bg-sky-500"></span>
                        <span class="font-black text-lg">Menu Riwayat</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1 items-start text-center">
                        @foreach ($historyMenus as $menu)
                            <a href="{{ $menu['url'] }}"
                                class="flex flex-col items-center justify-center
                                    gap-1 rounded-lg px-3 py-2">

                                <i
                                    class="{{ $menu['icon'] }}
                                        text-3xl font-bold
                                        text-{{ $menu['color'] }}-700
                                        bg-{{ $menu['color'] }}-300
                                        py-3 px-4 rounded-2xl"></i>

                                <span class="font-extrabold text-sm">
                                    {{ $menu['label'] }}
                                </span>

                            </a>
                        @endforeach
                    </div>
                </section>

            </div>
        </div>

        {{-- Tombol Pulang --}}
        @if ($absenP && $absenP?->absensi_type_pulang == null)
            <div
                class="fixed inset-x-0 bottom-0 z-30 hidden
                flex justify-center
                px-4 pb-4
                pointer-events-none">
                <div
                    class="pointer-events-auto
                    w-full max-w-sm
                    rounded-2xl
                    bg-white/80
                    p-1.5
                    shadow-lg
                    ring-1 ring-slate-200/70
                    backdrop-blur-md">
                    <button id="modalPulangBtn" data-absen="{{ $absenP }}" type="button"
                        class="group flex w-full
                        items-center justify-center gap-2
                        rounded-xl
                        bg-red-500
                        px-5 py-3
                        text-white
                        shadow-md shadow-red-500/20
                        transition-all duration-200
                        hover:bg-red-600
                        active:scale-[0.98]">
                        <span
                            class="flex h-9 w-9
                            items-center justify-center
                            rounded-lg
                            bg-white/15">
                            <i class="ri-run-line text-xl"></i>
                        </span>

                        <span class="text-sm font-extrabold">
                            Waktunya Pulang
                        </span>

                        <i
                            class="ri-arrow-right-line
                            text-lg opacity-80
                            transition-transform duration-200
                            group-hover:translate-x-1"></i>
                    </button>
                </div>
            </div>
        @endif

        @if ($kerjasamaId == 1 && session()->has('point'))
            <div class="flex items-center justify-end mx-5 mb-5">
                <div class="flex flex-row px-4 py-1 text-xs text-white rounded-md shadow-xs gap-x-2 sm:w-fit"
                    style="background-color: #0C642F">
                    <i class="ri-checkbox-circle-line"></i>
                    <span>{{ session('point') }}</span>
                </div>
            </div>
        @endif

        @include('dashboard.partials.prayer-modal')

        @include('dashboard.partials.checkout-modal')

        @include('dashboard.partials.news-modal')
        @include('dashboard.partials.checkpoint-reminder-modal')
        @include('dashboard.partials.checkpoint-rejected-modal')
        @include('dashboard.partials.checkpoint-pending-modal')
    </div>

    <div class="{{ $kontrak && $kontrak->isPending() ? 'hidden' : '' }} mx-5 mt-5 sm:mx-10">
        @if ($kontrak && $kontrak->isActive())
            {{-- APPROVED --}}
            <div
                class="mx-auto flex max-w-3xl flex-col-reverse gap-4 rounded-xl border border-white/70 bg-linear-to-br from-white to-green-50 p-4 shadow-lg shadow-slate-700/10 sm:p-5 md:flex-row md:items-center md:justify-between">
                <div class="w-full text-center md:text-left">
                    <span
                        class="mb-2 inline-flex rounded-full bg-green-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-green-700">
                        Approved
                    </span>
                    <h3 class="mb-2 text-lg font-black text-blue-700 sm:text-xl">
                        Status Pengajuan Saat Ini
                    </h3>

                    <p class="mb-4 text-sm leading-relaxed text-slate-700 sm:text-base">
                        Selamat! Pengajuan anda telah di setujui silahkan
                        lihat detailnya.
                    </p>

                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-center md:justify-start">
                        <a href="{{ route('form-kontrak-preview', ['token' => \Illuminate\Support\Facades\Crypt::encryptString($kontrak?->id)]) }}"
                            onclick="window.open(this.href, '_blank'); window.location.reload(); return false;"
                            class="rounded-2xl bg-yellow-400 px-4 py-2.5 text-center text-sm font-bold text-slate-900 shadow-xs transition hover:bg-yellow-500">
                            Lihat Detail
                        </a>

                        {{-- <a href="{{ route('form-kontrak-index', ['token' => \Illuminate\Support\Facades\Crypt::encryptString($kontrak?->id)]) }}"
                            class="rounded-2xl bg-green-600 px-4 py-2.5 text-center text-sm font-bold text-white shadow-xs transition hover:bg-green-700">
                            Tanda Tangan Kontrak
                        </a> --}}
                    </div>
                </div>

                <div
                    class="mx-auto flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-green-600 text-white shadow-lg shadow-green-700/20 sm:h-20 sm:w-20 md:mx-0">
                    <i class="ri-check-line text-4xl sm:text-5xl"></i>
                </div>
            </div>
        @elseif($kontrak && $kontrak->isPending())
            {{-- PENDING --}}
            <div
                class="mx-auto flex max-w-3xl flex-col-reverse gap-4 rounded-xl border border-white/70 bg-linear-to-br from-white to-blue-50 p-4 shadow-lg shadow-slate-700/10 sm:p-5 md:flex-row md:items-center md:justify-between">
                <div class="w-full text-center md:text-left">
                    <span
                        class="mb-2 inline-flex rounded-full bg-blue-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-blue-700">
                        Pending
                    </span>
                    <h3 class="mb-2 text-lg font-black text-blue-700 sm:text-xl">
                        Status Pengajuan Saat Ini
                    </h3>

                    <p class="text-sm leading-relaxed text-slate-700 sm:text-base">
                        Data anda telah terkirim ke HRD perusahaan,
                        jika belum di approve silahkan pengajuan kembali dalam
                        <span class="font-black text-red-600">
                            {{ max(0, 30 - (int) $kontrak->created_at->diffInDays(now())) }} Hari
                        </span>
                    </p>
                </div>

                <div
                    class="mx-auto flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-blue-500 text-white shadow-lg shadow-blue-700/20 sm:h-20 sm:w-20 md:mx-0">
                    <i class="ri-information-line text-4xl sm:text-5xl"></i>
                </div>
            </div>
        @elseif ($kontrak && $kontrak->isProses())
            {{-- PROSES --}}
            <div
                class="mx-auto flex max-w-3xl flex-col-reverse gap-4 rounded-xl border border-white/70 bg-linear-to-br from-white to-blue-50 p-4 shadow-lg shadow-slate-700/10 sm:p-5 md:flex-row md:items-center md:justify-between">
                <div class="w-full text-center md:text-left">
                    <span
                        class="mb-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-amber-700">
                        Proses
                    </span>
                    <h3 class="mb-2 text-lg font-black text-amber-700 sm:text-xl">
                        Status Pengajuan Saat Ini
                    </h3>

                    <p class="text-sm leading-relaxed text-slate-700 sm:text-base">
                        Data anda telah terkirim ke HRD perusahaan,
                        dan sedang di proses oleh HRD, harap Ditunggu.
                    </p>
                </div>

                <div
                    class="mx-auto flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-amber-500 text-white shadow-lg shadow-amber-700/20 sm:h-20 sm:w-20 md:mx-0">
                    <i class="ri-time-line text-4xl sm:text-5xl"></i>
                </div>
            </div>
        @endif
    </div>
    @include('dashboard.partials.scripts.checkout-tracking')
    @include('dashboard.partials.scripts.dashboard-ui')
    @include('dashboard.partials.scripts.shift-timer')
    <x-flasher-theme />

    <script>
        navigator.geolocation.getCurrentPosition(
            position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                updateHeroTimezone(lat, lng);
            },
            error => {
                console.warn('GPS tidak tersedia:', error);

                // fallback timezone perangkat
                updateHeroTimezone();
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );

        function updateHeroTimezone(lat = null, lng = null) {
            let timezone = Intl.DateTimeFormat()
                .resolvedOptions()
                .timeZone;

            let label = 'WIB';

            // Jika timezone perangkat sudah Indonesia
            if (timezone === 'Asia/Jakarta') {
                label = 'WIB';
            } else if (timezone === 'Asia/Makassar') {
                label = 'WITA';
            } else if (timezone === 'Asia/Jayapura') {
                label = 'WIT';
            }

            document.getElementById('heroTimezone').textContent = label;

            updateHeroClock(timezone);
        }

        function updateHeroClock(timezone) {
            const clock = document.getElementById('heroClock');

            if (!clock) return;

            const time = new Intl.DateTimeFormat('en-GB', {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).format(new Date());

            clock.textContent = time;
        }

        setInterval(() => {
            const timezone = Intl.DateTimeFormat()
                .resolvedOptions()
                .timeZone;

            updateHeroClock(timezone);
        }, 1000);
    </script>
    <script>
        const toggle = document.getElementById('mainToggle');
        const indicator = document.getElementById('toggleIndicator');
        const track = document.getElementById('dashboardPanelTrack');
        const buttons = toggle.querySelectorAll('.toggle-btn');

        function setActive(index, animate = true) {

            const button = buttons[index];

            // Posisi indicator mengikuti tombol
            indicator.style.left =
                `${button.offsetLeft}px`;

            // Animasi indicator dan panel
            indicator.style.transition = animate ?
                'left 300ms ease-in-out' :
                'none';
            track.style.transition = animate ?
                'transform 300ms ease-in-out' :
                'none';
            track.style.transform = `translateX(-${index * (100 / track.children.length)}%)`;

            buttons.forEach((btn, i) => {

                if (i === index) {
                    btn.classList.add(
                        'font-extrabold',
                        'text-white'
                    );

                    btn.classList.remove(
                        'text-slate-700'
                    );

                } else {
                    btn.classList.remove(
                        'font-extrabold',
                        'text-white'
                    );

                    btn.classList.add(
                        'text-slate-700'
                    );
                }
            });
        }


        // Default = Utama
        setActive(1, false);


        // Klik tombol
        buttons.forEach((button, index) => {

            button.addEventListener('click', () => {
                setActive(index);
            });

        });
    </script>
</body>

</html>
