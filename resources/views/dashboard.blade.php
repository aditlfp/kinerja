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
        $userId         = $user->id;
        $jabatan        = $user->jabatan;
        $jabatanId      = $user->jabatan_id;
        $roleId         = $roleId;
        $kerjasamaId    = $kerjasamaId;
        $devisiId       = $user->devisi_id;
        $directJabatan  = $directJabatan;


        /*
        |--------------------------------------------------------------------------
        | ACCESS / ROLE
        |--------------------------------------------------------------------------
        */
        $isMitra        = $jabatan === 'MITRA';
        $isLeader       = $jabatan === 'LEADER';
        $isDireksi      = $jabatan === 'DIREKSI';

        $isCoCs         = $jabatan === 'CO-CS';
        $isCoScr        = $jabatan === 'CO-SCR';
        $isSpvW         = $jabatan === 'SPV-W';

        $isKerjasamaSac = $kerjasamaId == 1;
        $isAdmin        = $roleId == 2;


        /*
        |--------------------------------------------------------------------------
        | ABSENSI
        |--------------------------------------------------------------------------
        */
        $hariIni = now()->dayOfWeekIso;

        $isWeekend = in_array($hariIni, [6, 7]);

        $tampilkanAbsensi =
            !$isWeekend ||
            !$isKerjasamaSac ||
            $devisiId != 26;


        /*
        |--------------------------------------------------------------------------
        | ABSENSI ROUTE
        |--------------------------------------------------------------------------
        */
        $absensiRoute = match ($jabatan) {
            'CO-CS'  => 'absensi-karyawan-co-cs.index',
            'CO-SCR' => 'absensi-karyawan-co-scr.index',
            default  => null,
        };


        /*
        |--------------------------------------------------------------------------
        | REKAP
        |--------------------------------------------------------------------------
        */
        $jabatanRekap = [
            'CO-CS',
            'CO-SCR',
            'SPV-W',
        ];

        $bisaLihatRekap =
            in_array($jabatan, $jabatanRekap) &&
            $userId != 7;

        $rekapRoute = $isSpvW
            ? 'spvw.rekap.index'
            : 'index.rekap.data.leader';


        /*
        |--------------------------------------------------------------------------
        | KONTRAK
        |--------------------------------------------------------------------------
        */
        $kontrakSelesai = $kontrak?->tgl_selesai_kontrak
            ? \Carbon\Carbon::parse($kontrak->tgl_selesai_kontrak)->isPast()
            : false;

        $kontrakBerjalan =
            $kontrak &&
            (
                $kontrak->isPending() ||
                $kontrak->isActive() ||
                $kontrak->isProses()
            );

        $bisaAjukanKontrak =
            !$kontrak ||
            $kontrakSelesai ||
            !$kontrakBerjalan;


        /*
        |--------------------------------------------------------------------------
        | MENU KHUSUS
        |--------------------------------------------------------------------------
        */
        $bisaLihatMenuManajemen =
            in_array($jabatanId, [4, 14]);

        $bisaLihatMenuSlip =
            in_array($userId, [7, 10, 5]);


        /*
        |--------------------------------------------------------------------------
        | MENU ROLE
        |--------------------------------------------------------------------------
        */
        $routes = [
            'MITRA' => [
                'karyawan'        => 'mitra_user',
                'jadwal'          => $isAdmin
                    ? 'admin.jadwal.index'
                    : 'mitra_jadwal',
                'absensi'         => 'mitra_absensi',
                'izin'            => 'mitra_izin',
                'lembur'          => 'mitra_lembur',
                'laporan'         => 'mitra_laporan',
                'rating'          => 'mitra-rating.index',
                'laporan bulanan' => 'mitra-laporan-bulanan.index',
            ],

            'LEADER' => [
                'karyawan' => 'lead_user',
                'jadwal'   => $isAdmin
                    ? 'admin.jadwal.index'
                    : 'leader-jadwal.index',
                'absensi' => 'lead_absensi',
                'izin'    => 'lead_izin',
                'lembur'  => 'lead_lembur',
                'laporan' => 'lead_laporan',
                'rating'  => 'leader-rating.index',
            ],

            'DIREKSI' => [
                'karyawan' => 'direksi_user',
                'jadwal'   => $isAdmin
                    ? 'admin.jadwal.index'
                    : 'direksi_jadwal',
                'absensi' => 'direksi_absensi',
                'izin'    => 'direksi_izin',
                'lembur'  => 'direksi_lembur',
                'laporan' => 'direksi_laporan',
                'rating'  => 'direksi-rating.index',
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
            'karyawan'        => 'ri-pass-pending-line',
            'jadwal'          => 'ri-calendar-check-line',
            'absensi'         => 'ri-todo-line',
            'izin'            => 'ri-shield-user-line',
            'lembur'          => 'ri-time-line',
            'laporan'         => 'ri-image-add-line',
            'laporan bulanan' => 'ri-image-add-line',
            'rating'          => 'ri-sparkling-line',
            'kinerja'         => 'ri-sparkling-line',
            'kontrak'         => 'ri-pass-pending-line',
        ];
    @endphp
    @php
        /*
        |--------------------------------------------------------------------------
        | DASHBOARD MENU UTAMA
        |--------------------------------------------------------------------------
        */

        $dashboardMenus = [
            [
                'key'   => 'kehadiran',
                'label' => 'Kehadiran',
                'route' => 'absensi.index',
                'icon'  => 'ri-calendar-line',
                'color' => 'emerald',
            ],

            [
                'key'   => 'izin',
                'label' => 'Izin',
                'route' => 'izin.index',
                'icon'  => 'ri-file-line',
                'color' => 'blue',
            ],

            [
                'key'   => 'kinerja',
                'label' => 'Kinerja',
                'route' => 'checkpoint-user.index',
                'icon'  => 'ri-bar-chart-line',
                'color' => 'amber',
            ],

            [
                'key'   => 'rating',
                'label' => 'Rating',
                'route' => 'ratingSaya',
                'params' => [$user->id],
                'icon'  => 'ri-star-line',
                'color' => 'indigo',
            ],

            [
                'key'   => 'laporan',
                'label' => 'Laporan',
                'route' => 'laporan.index',
                'icon'  => 'ri-file-text-line',
                'color' => 'sky',
            ],

            [
                'key'   => 'kontrak',
                'label' => 'Pengajuan Kontrak',
                'route' => 'form-kontrak-request',
                'icon'  => 'ri-file-add-line',
                'color' => 'green',
                'show'  => $bisaAjukanKontrak,
            ],

            [
                'key'   => 'alquran',
                'label' => "Al Qur'an",
                'url'   => 'https://baca-alquran.sac-po.com',
                'icon'  => 'ri-book-line',
                'color' => 'blue',
            ],

            [
                'key'   => 'sppd',
                'label' => 'SPPD',
                'url'   => 'https://sppd-online.sac-po.com/login',
                'icon'  => 'ri-road-map-line',
                'color' => 'sky',
                'show'  => $isKerjasamaSac,
            ],
        ];

        // Hanya tampilkan menu yang memenuhi kondisi
        $dashboardMenus = collect($dashboardMenus)
            ->filter(fn ($menu) => $menu['show'] ?? true)
            ->values();
    @endphp
    <div class="min-h-screen pb-24">
        {{-- @include('../layouts/navbar') --}}

        {{-- navbar --}}
        <div class="pt-5 pb-3 px-6 space-y-4">
            <div class="flex items-center justify-between">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" srcset="" class="w-32 h-auto">
                <div class="flex items-center justify-center gap-2">
                    <a href="#"
                        class="flex items-center gap-2 bg-amber-500/10 border border-amber-500/15 py-1 px-3 rounded-3xl">
                        <i class="ri-wallet-3-line text-xl"></i>
                        <span class="font-medium">Slip</span>
                    </a>
                    <form action="{{ route('logout') }}" method="post"
                        class="min-w-0 bg-neutral-500/10 border border-neutral-500/15 py-1 px-3 rounded-2xl">
                        @csrf
                        @method('POST')
                        <button type="submit"
                            class="flex w-full flex-col items-center gap-1 rounded-lg text-slate-500 transition hover:bg-red-50">
                            <i class="ri-logout-box-r-line text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div>
                <div class="flex items-center font-extrabold text-lg gap-2">
                    <i class="ri-user-line"></i>
                    <span>
                        Halo, {{ $user->nama_lengkap }}
                    </span>
                </div>
            </div>
        </div>

        {{-- status card --}}
        <div class="flex flex-col items-center justify-center w-full gap-2 px-4 py-2 font-semibold text-center">
            @if (!$absenP)
                <span class="flex items-center gap-2 bg-rose-500 text-white px-4 py-2 rounded-lg"><i
                        class="ri-information-line text-xl"></i> Kamu belum melakukan presensi hari ini</span>
            @elseif($absenP->shift && $absenP->shift->is_overnight)
                <span class="flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-lg"><i
                        class="ri-time-line text-xl"></i> Kamu sedang dalam shift</span>
            @else
                <span class="flex items-center gap-2 bg-emerald-500 text-white px-4 py-2 rounded-lg"><i
                        class="ri-check-double-line text-xl"></i> Kamu sudah melakukan presensi hari ini</span>
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
                <section class="w-1/3 shrink-0"></section>

                {{-- menu utama --}}
                <section class="w-1/3 shrink-0">
                    <div class="px-6 py-2 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-1 h-5 rounded-full bg-sky-500"></span>
                            <span class="font-black text-lg">Menu Utama</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1 items-start text-center">
                            @foreach ($dashboardMenus as $menu)

                                <a
                                    href="{{ isset($menu['url'])
                                        ? $menu['url']
                                        : route($menu['route'], $menu['params'] ?? []) }}"
                                    class="flex flex-col items-center justify-center
                                        gap-1 rounded-lg px-3 py-2"
                                >

                                    <i
                                        class="{{ $menu['icon'] }}
                                            text-3xl font-bold
                                            text-{{ $menu['color'] }}-700
                                            bg-{{ $menu['color'] }}-300
                                            py-3 px-4 rounded-2xl"
                                    ></i>

                                    <span class="font-extrabold text-sm">
                                        {{ $menu['label'] }}
                                    </span>

                                </a>

                            @endforeach
                        </div>
                    </div>


                </section>

                {{-- menu riwayat --}}
                <section class="w-1/3 shrink-0 px-6 py-2 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-1 h-5 rounded-full bg-sky-500"></span>
                        <span class="font-black text-lg">Menu Riwayat</span>
                    </div>
                    <div class="grid grid-cols-4 gap-1 items-start text-center">
                        <a href=""
                            class="flex flex-col items-center justify-center gap-1 rounded-lg px-3 py-2">
                            <i
                                class="ri-calendar-line text-3xl font-bold text-emerald-700 bg-emerald-300 py-3 px-4 rounded-2xl"></i>
                            <span class="font-extrabold text-sm">Kehadiran</span>
                        </a>
                        <a href=""
                            class="flex flex-col items-center justify-center gap-1 rounded-lg px-3 py-2">
                            <i
                                class="ri-file-line text-3xl font-bold text-blue-700 bg-blue-300 py-3 px-4 rounded-2xl"></i>
                            <span class="font-extrabold text-sm">Izin</span>
                        </a>
                        <a href=""
                            class="flex flex-col items-center justify-center gap-1 rounded-lg px-3 py-2">
                            <i
                                class="ri-bar-chart-line text-3xl font-bold text-amber-700 bg-amber-300 py-3 px-4 rounded-2xl"></i>
                            <span class="font-extrabold text-sm">Kinerja</span>
                        </a>
                        <a href=""
                            class="flex flex-col items-center justify-center gap-1 rounded-lg px-3 py-2">
                            <i
                                class="ri-file-text-line text-3xl font-bold text-sky-700 bg-sky-300 py-3 px-4 rounded-2xl"></i>
                            <span class="font-extrabold text-sm">Laporan</span>
                        </a>
                    </div>
                </section>

            </div>
        </div>

        <div class="flex items-center justify-start">
            @if ($absenP && $absenP?->absensi_type_pulang == null)
                <div id="statusAbsensiContainer"
                    class="inset-0 px-4 py-2 ml-5 font-semibold text-center rounded-tr-lg rounded-bl-lg shadow-md w-fit"
                    style="background-color: #fafafa; font-size: 10pt; margin-bottom: 10px;">
                    <p id="statusAbsensiText">Memeriksa status...</p>
                </div>
            @endif

            @if ($izin)
                <span class="hidden">
                    <span id="waktuIzin" data-waktu="{{ $izin->updated_at->format('H:i') }}"></span>
                </span>
                <div id="inpoIzin"
                    class="text-center hidden rounded-tr-lg rounded-bl-lg my-5 sm:w-fit text-md sm:text-xl font-semibold text-slate-50 {{ $statusClass }} py-2 px-4 shadow-md ml-5 sm:ml-10 inset-0">
                    <p>{{ $statusMessage }}</p>
                </div>
            @endif
        </div>

        @if ($kerjasamaId == 1 && session()->has('point'))
            <div class="flex items-center justify-end mx-5 mb-5">
                <div class="flex flex-row px-4 py-1 text-xs text-white rounded-md shadow-sm gap-x-2 sm:w-fit"
                    style="background-color: #0C642F">
                    <i class="ri-checkbox-circle-line"></i>
                    <span>{{ session('point') }}</span>
                </div>
            </div>
        @endif

        @include('dashboard.partials.prayer-modal')
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <main>
                <div class="mx-auto max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                            {{-- Handle Check Kode Jabatan --}}
                            @if ($jabatan != 'MITRA' && $jabatan != 'LEADER' && $jabatan != 'DIREKSI')
                                {{-- absensi --}}
                                <div id="btnAbsensi"
                                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-blue-200 border-l-4 border-l-blue-500 bg-gradient-to-r from-blue-50 to-white text-blue-900 shadow-sm transition hover:-translate-y-0.5 hover:from-blue-100 hover:shadow-md">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 transition group-hover:bg-blue-500 group-hover:text-white"><i
                                            class="ri-calendar-check-line text-lg"></i></span>
                                    <button class="text-sm font-semibold">Kehadiran</button>
                                </div>
                                {{-- menu menu dashboard absensi --}}
                                @php
                                    $hariIni = Carbon\Carbon::now()->format('N');
                                    $tampilkanAbsensi =
                                        !in_array($hariIni, [6, 7]) || $kerjasamaId != 1 || $user->devisi_id != 26;
                                    $codeJabatan = $jabatan;
                                    $absensiRoute = match ($codeJabatan) {
                                        'CO-CS' => 'absensi-karyawan-co-cs.index',
                                        'CO-SCR' => 'absensi-karyawan-co-scr.index',
                                        default => '',
                                    };
                                @endphp
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="ngabsen">
                                    <a href="{{ $tampilkanAbsensi ? route('absensi.index') : 'javascript:void(0);' }}"
                                        class="group flex w-full items-center justify-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 font-medium text-blue-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-100"><i
                                            class="ri-calendar-check-line text-blue-600 transition group-hover:scale-110"></i>{{ $tampilkanAbsensi ? 'Kehadiran' : 'Tidak Ada Jadwal' }}</a>
                                </div>
                                @if (!empty($absensiRoute))
                                    <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="ngabsenK">
                                        <a href="{{ route($absensiRoute) }}"
                                            class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-white"><i
                                                class="ri-team-line"></i>Kehadiran Karyawan</a>
                                    </div>
                                @endif
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="ngIzin">
                                    <a href="{{ route('izin.create') }}"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-white"><i
                                            class="ri-calendar-close-line"></i>Izin</a>
                                </div>
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="btnRiwayat">
                                    <a href="javascript:void(0);"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-white"><i
                                            class="ri-history-line"></i>Riwayat</a>
                                </div>
                                <div class="hidden w-full px-4 space-y-4 overflow-hidden sm:px-20" id="isiAbsen">
                                    <a href="historyAbsensi"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-white"><i
                                            class="ri-history-line"></i>Riwayat Kehadiran</a>
                                </div>
                                <div class="hidden w-full px-4 space-y-4 overflow-hidden sm:px-20" id="isiLembur">
                                    <a href="{{ route('lemburIndexUser') }}"
                                        class="group flex w-full items-center justify-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 font-medium text-orange-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-100"><i
                                            class="ri-time-line text-orange-600 transition group-hover:scale-110"></i>Riwayat
                                        Lembur</a>
                                </div>
                                <div class="hidden w-full px-4 space-y-4 overflow-hidden sm:px-20" id="isiIzin">
                                    <a href="{{ route('izin.index') }}"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-white"><i
                                            class="ri-file-list-3-line"></i>Riwayat Izin</a>
                                </div>
                        </div>
                        {{-- Handle Rekap & Lembur --}}
                        @php
                            $jabatanMatch = match ($codeJabatan) {
                                'CO-CS' => true,
                                'CO-SCR' => true,
                                'SPV-W' => true,
                                default => false,
                            };
                        @endphp
                        @if ($jabatanMatch && $user->id != '7')
                            <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                                <div id="btnRekap"
                                    onclick="window.location='{{ $codeJabatan == 'SPV-W' ? route('spvw.rekap.index') : route('index.rekap.data.leader') }}'"
                                    class="w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                    <i class="ri-calendar-schedule-line"></i>
                                    <button class="text-sm font-bold uppercase">
                                        Data Rekap (Trial)
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- TO Manajemen SPV And MRT --}}

                        @if (in_array($user->jabatan_id, [4, 14]))
                            <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                                <div id="btnRekap" onclick="window.location='{{ route('manajemen_rekap') }}'"
                                    class="w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                    <i class="ri-calendar-schedule-line"></i>
                                    <button class="text-sm font-bold uppercase">
                                        Data Rekap
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- End Handle --}}
                        <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                            @if ($kerjasamaId == 1)
                                <div id="btnCP"
                                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 border-l-4 border-l-emerald-500 bg-gradient-to-r from-emerald-50 to-white text-emerald-900 shadow-sm transition hover:-translate-y-0.5 hover:from-emerald-100 hover:shadow-md">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white"><i
                                            class="ri-task-line text-lg"></i></span>
                                    <button class="text-sm font-semibold">
                                        Kinerja Harian
                                    </button>
                                </div>
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="isiIndex">
                                    <a href="/checkpoint-user"
                                        class="group flex w-full items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 font-medium text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100"><i
                                            class="ri-add-line text-emerald-600 transition group-hover:rotate-90"></i>Bukti
                                        Kerja</a>
                                </div>
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="tambahCP">
                                    <a href="{{ route('checkpoint-user.history') }}"
                                        class="group flex w-full items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 font-medium text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100"><i
                                            class="ri-history-line text-emerald-600 transition group-hover:scale-110"></i>Riwayat
                                        Bukti Kerja</a>
                                </div>
                                {{-- <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="tambahCP">
                                    <!--<a href="{{ route('checkpoint-user.create') }}" class="w-full btn btn-info" {{ \Carbon\Carbon::now()->isWeekend() ? '' : 'disabled' }}>Tambah Planning (sabtu - minggu )</a>-->
                                    <a href="{{ $cex ? route('checkpoint-user.edit', $cex->id) : route('checkpoint-user.create') }}"
                                        class="w-full btn btn-info">{{ $cex ? 'Ubah Rencana Kerja' : 'Tambah Rencana Kerja' }}
                                        (sabtu - minggu )</a>
                                </div>
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="kirimCP">
                                    <!--<a href="{{ route('checkpoint-user.create') }}" class="w-full btn btn-info" {{ \Carbon\Carbon::now()->isWeekend() ? 'disabled' : '' }}>Kirim Bukti (senin - jum'at)</a>-->
                                    <a href="{{ route('editBukti-checkpoint-user') }}" {{ !$cex ? 'disabled' : '' }}
                                        style="{{ !$cex ? 'background: #7dd3fc; border: none; color: #1e293b;' : '' }}"
                                        class="w-full btn btn-info ">{{ $cex ? 'Kirim Bukti' : 'Buat Rencana Kerja Terlebih Dahulu' }}
                                        (senin - jum'at)</a>
                                </div> --}}
                            @else
                                <div class="w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s"
                                    disabled>
                                    <i class="ri-list-check-3"></i>
                                    <button class="text-sm font-bold uppercase" disabled>
                                        Kinerja harian
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                            <div id="btnRating"
                                class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-violet-200 border-l-4 border-l-violet-500 bg-gradient-to-r from-violet-50 to-white text-violet-900 shadow-sm transition hover:-translate-y-0.5 hover:from-violet-100 hover:shadow-md">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-violet-600 transition group-hover:bg-violet-500 group-hover:text-white"><i
                                        class="ri-star-smile-line text-lg"></i></span>
                                <button class="text-sm font-semibold">Rating</button>
                            </div>
                            <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="cekMe">
                                <a href="{{ route('ratingSaya', $user->id) }}"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-white"><i
                                        class="ri-user-star-line"></i>Check Rating Saya</a>
                            </div>
                            @if ($roleId == 2)
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="cekRate">
                                    <a href="{{ route('admin.rating.index') }}"
                                        class="w-full btn btn-info">Rating</a>
                                </div>
                            @elseif($jabatan == 'LEADER')
                                <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="cekRate">
                                    <a href="{{ route('leader-rating.index') }}"
                                        class="w-full btn btn-info">Rating</a>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                            <div id="btnLaporan"
                                class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-orange-200 border-l-4 border-l-orange-500 bg-gradient-to-r from-orange-50 to-white text-orange-900 shadow-sm transition hover:-translate-y-0.5 hover:from-orange-100 hover:shadow-md">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600 transition group-hover:bg-orange-500 group-hover:text-white"><i
                                        class="ri-chat-3-line text-lg"></i></span>
                                <button class="text-sm font-semibold">Laporan</button>
                            </div>
                            <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="tambahLaporan">
                                <a href="https://laporan-sac.sac-po.com"
                                    class="group flex w-full items-center justify-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 font-medium text-orange-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-100"><i
                                        class="ri-add-line text-orange-600 transition group-hover:rotate-90"></i>Tambah
                                    Laporan</a>
                            </div>
                            <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16" id="cekLaporan">
                                <a href="https://laporan-sac.sac-po.com"
                                    class="group flex w-full items-center justify-center gap-2 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 font-medium text-orange-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-100"><i
                                        class="ri-history-line text-orange-600 transition group-hover:scale-110"></i>Riwayat
                                    Laporan</a>
                            </div>

                        </div>

                        {{-- Button Pengajuan Kontrak --}}
                        @php
                            $kontrakSelesai = $kontrak?->tgl_selesai_kontrak
                                ? \Carbon\Carbon::parse($kontrak->tgl_selesai_kontrak)->isPast()
                                : false;
                            $kontrakBerjalan =
                                $kontrak && ($kontrak->isPending() || $kontrak->isActive() || $kontrak->isProses());
                            $bisaAjukanKontrak = !$kontrak || $kontrakSelesai || !$kontrakBerjalan;
                        @endphp
                        <div
                            class="{{ $bisaAjukanKontrak ? 'flex' : 'hidden' }} flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                            <a href="{{ $bisaAjukanKontrak ? route('form-kontrak-request') : 'javascript:void(0)' }}"
                                class="group {{ $bisaAjukanKontrak ? 'border border-amber-200 bg-gradient-to-r from-amber-50 to-white text-amber-900 hover:-translate-y-0.5 hover:from-amber-100 hover:shadow-md' : 'cursor-not-allowed bg-slate-200 text-slate-500' }} flex h-12 w-full items-center justify-center gap-2 rounded-xl border-l-4 border-l-amber-500 font-semibold shadow-sm transition">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 transition group-hover:rotate-6 group-hover:bg-amber-500 group-hover:text-white"><i
                                        class="ri-file-edit-line text-lg"></i></span>
                                <span class="text-sm">Pengajuan Kontrak</span>
                            </a>
                        </div>

                        @if ($user->id == 7 || $user->id == 10 || $user->id == 5)
                            <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                                <a href="{{ route('slip-karyawan') }}"
                                    class=" w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                    <i class="text-xl ri-wallet-3-line"></i>
                                    <span class="text-sm font-bold uppercase">Slip Gaji Karyawan</span>
                                </a>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                                <a href="{{ route('manajemen_absensi') }}"
                                    class=" w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                    <i class="text-xl ri-wallet-3-line"></i>
                                    <span class="text-sm font-bold uppercase">Absensi Karyawan</span>
                                </a>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                                <a href="{{ route('manajemen_laporan') }}"
                                    class=" w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                    <i class="text-xl ri-wallet-3-line"></i>
                                    <span class="text-sm font-bold uppercase">Laporan Karyawan</span>
                                </a>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-2 px-2 pt-2 overflow-hidden">
                                <a href="{{ route('manajemen_user') }}"
                                    class=" w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                    <i class="text-xl ri-wallet-3-line"></i>
                                    <span class="text-sm font-bold uppercase">Data Karyawan</span>
                                </a>
                            </div>
                        @endif

                        @if ($jabatan == 'CO-CS')
                            <div class="flex items-center w-full px-5 mt-5 space-y-4 overflow-hidden sm:px-16">
                                <a href="{{ route('leaderView') }}" class="w-full btn btn-info"><i
                                        class="text-xl ri-pass-pending-line"></i>Menu Leader</a>
                            </div>
                        @elseif($jabatan == 'CO-SCR')
                            <div class="flex items-center w-full mt-5 space-y-4 overflow-hidden sm:px-16">
                                <a href="{{ route('danruView') }}" class="w-full btn btn-info"><i
                                        class="text-xl ri-pass-pending-line"></i>Menu Danru</a>
                            </div>
                        @elseif($directJabatan == 'SPV-W')
                            <div class="flex items-center w-full mt-5 space-y-4 overflow-hidden sm:px-16">
                                <a href="{{ route('SPVWiew') }}" class="w-full btn btn-info"><i
                                        class="text-xl ri-pass-pending-line"></i>Menu SPV Wilayah</a>
                            </div>
                        @endif
                    @else
                        @php
                            $routes = [
                                'MITRA' => [
                                    'karyawan' => 'mitra_user',
                                    'jadwal' => $roleId == 2 ? 'admin.jadwal.index' : 'mitra_jadwal',
                                    'absensi' => 'mitra_absensi',
                                    'izin' => 'mitra_izin',
                                    'lembur' => 'mitra_lembur',
                                    'laporan' => 'mitra_laporan',
                                    'rating' => 'mitra-rating.index',
                                    'laporan bulanan' => 'mitra-laporan-bulanan.index',
                                ],
                                'LEADER' => [
                                    'karyawan' => 'lead_user',
                                    'jadwal' => $roleId == 2 ? 'admin.jadwal.index' : 'leader-jadwal.index',
                                    'absensi' => 'lead_absensi',
                                    'izin' => 'lead_izin',
                                    'lembur' => 'lead_lembur',
                                    'laporan' => 'lead_laporan',
                                    'rating' => 'leader-rating.index',
                                ],
                                'DIREKSI' => [
                                    'karyawan' => 'direksi_user',
                                    'jadwal' => $roleId == 2 ? 'admin.jadwal.index' : 'direksi_jadwal',
                                    'absensi' => 'direksi_absensi',
                                    'izin' => 'direksi_izin',
                                    'lembur' => 'direksi_lembur',
                                    'laporan' => 'direksi_laporan',
                                    'rating' => 'direksi-rating.index',
                                    'kinerja' => 'direksi.cp.index',
                                    'kontrak' => 'direksi-cekKontrak',
                                ],
                            ];
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
                        @if (array_key_exists($jabatan, $routes))
                            <div class="w-full gap-2">
                                @if ($jabatan == 'DIREKSI')
                                    {{-- absensi --}}
                                    <div id="btnAbsensi"
                                        class="w-full flex justify-center items-center gap-2 bg-amber-400 rounded-md h-11 hover:bg-amber-500 transition-all ease-linear .2s">
                                        <i class="text-xl ri-todo-line"></i>
                                        <button class="text-sm font-bold uppercase">Kehadiran</button>
                                    </div>
                                    {{-- menu menu dashboard absensi --}}
                                    @php
                                        $hariIni = Carbon\Carbon::now()->format('N');
                                        $tampilkanAbsensi = !in_array($hariIni, [6, 7]) || $kerjasamaId != 1;
                                        $codeJabatan = $jabatan;
                                        $absensiRoute = match ($codeJabatan) {
                                            'CO-CS' => 'absensi-karyawan-co-cs.index',
                                            'CO-SCR' => 'absensi-karyawan-co-scr.index',
                                            default => null,
                                        };
                                    @endphp
                                    <div class="flex flex-col gap-2 mt-2">
                                        <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16"
                                            id="ngabsen">
                                            <a href="{{ $tampilkanAbsensi ? route('absensi.index') : 'javascript:void(0);' }}"
                                                class="w-full btn btn-info">{{ $tampilkanAbsensi ? 'Kehadiran' : 'Tidak Ada Jadwal' }}</a>
                                        </div>
                                        @if ($absensiRoute)
                                            <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16"
                                                id="ngabsenK">
                                                <a href="{{ route($absensiRoute) }}"
                                                    class="w-full btn btn-info">Kehadiran karyawan</a>
                                            </div>
                                        @endif
                                        <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16"
                                            id="ngIzin">
                                            <a href="{{ route('izin.create') }}" class="w-full btn btn-info">Izin</a>
                                        </div>
                                        <div class="hidden w-full px-2 space-y-4 overflow-hidden sm:px-16"
                                            id="btnRiwayat">
                                            <a href="#" class="w-full btn btn-success">Riwayat</a>
                                        </div>
                                        <div class="hidden w-full px-4 space-y-4 overflow-hidden sm:px-20"
                                            id="isiAbsen">
                                            <a href="historyAbsensi" class="w-full btn btn-info">Riwayat Kehadiran</a>
                                        </div>
                                        <div class="hidden w-full px-4 space-y-4 overflow-hidden sm:px-20"
                                            id="isiLembur">
                                            <a href="{{ route('lemburIndexUser') }}"
                                                class="w-full btn btn-info">Riwayat Lembur</a>
                                        </div>
                                        <div class="hidden w-full px-4 space-y-4 overflow-hidden sm:px-20"
                                            id="isiIzin">
                                            <a href="{{ route('izin.index') }}" class="w-full btn btn-info">Riwayat
                                                Izin</a>
                                        </div>
                                    </div>
                                @endif

                                <div
                                    class="grid w-full grid-cols-2 gap-2 mt-5 space-y-0 overflow-hidden sm:grid-cols-3">
                                    @foreach ($routes[$jabatan] as $key => $route)
                                        <div class="w-full md:space-y-4 overflow-hidden rounded-lg {{ $key == 'rating' && $jabatan == 'DIREKSI' ? 'hidden' : '' }}"
                                            id="L{{ $key }}">
                                            <a href="{{ route($route) }}"
                                                class="relative flex h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                                                <i
                                                    class="{{ $icons[$key] }} text-xl text-sky-600"></i>{{ ucfirst($key) }}
                                                @if ($jabatan == 'DIREKSI' && $key == 'kinerja')
                                                    <span class="absolute text-center bg-yellow-500"
                                                        style="padding: 20px 25px 5px 35px; right: -20px; top: -18px; transform: rotate(35deg);">
                                                        <p style="transform: rotate(-35deg);">
                                                            {{ $totcex }}
                                                        </p>
                                                    </span>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @endif

                        {{-- quran --}}
                        <div class="flex items-center justify-center w-full gap-x-2">
                            <div id="btnAbsi" style="{{ $kerjasamaId != 1 ?: 'width: 50%;' }}"
                                class="group mt-5 flex h-12 items-center justify-center gap-2 rounded-xl border border-sky-200 border-l-4 border-l-sky-500 bg-gradient-to-r from-sky-50 to-white px-2 font-semibold text-sky-900 shadow-sm transition hover:-translate-y-0.5 hover:from-sky-100 hover:shadow-md">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-600 transition group-hover:bg-sky-500 group-hover:text-white"><i
                                        class="ri-book-open-line text-lg"></i></span>
                                <a href="https://baca-alquran.sac-po.com" class="text-sm font-bold uppercase">
                                    {{ $kerjasamaId != 1 ? 'Baca Al-Qur`an' : 'Al-Qur`an' }}
                                </a>
                            </div>
                            <div style="{{ $kerjasamaId != 1 ?: 'width: 50%;' }}"
                                class="group {{ $kerjasamaId == 1 ? 'flex' : 'hidden' }} mt-5 h-12 items-center justify-center gap-2 rounded-xl border border-cyan-200 border-l-4 border-l-cyan-500 bg-gradient-to-r from-cyan-50 to-white px-5 font-semibold text-cyan-900 shadow-sm transition hover:-translate-y-0.5 hover:from-cyan-100 hover:shadow-md">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600 transition group-hover:bg-cyan-500 group-hover:text-white"><i
                                        class="ri-file-text-line text-lg"></i></span>
                                <a href="https://sppd-online.sac-po.com/login" class="text-sm font-bold uppercase">
                                    SPPD
                                </a>
                            </div>
                        </div>

                        @include('dashboard.partials.checkout-modal')
                    </div>
                </div>

                @include('dashboard.partials.news-modal')
                @include('dashboard.partials.checkpoint-reminder-modal')
                @include('dashboard.partials.checkpoint-rejected-modal')
                @include('dashboard.partials.checkpoint-pending-modal')

                {{-- <div class="pointer-events-none fixed inset-x-0 bottom-0 z-[999] flex justify-center px-3 pb-3">
                    <div class="pointer-events-auto w-full max-w-md">
                        <x-menu-mobile :cekAbsen="$cekAbsen" />
                    </div>
                </div> --}}
            </main>
        </div>
        @if (count($warn) >= 3)
            <div class="flex justify-center pt-10 mx-10 sm:justify-start">
                <div
                    class="inset-0 flex flex-col justify-start px-4 py-2 mb-5 font-semibold text-white bg-red-500 rounded-lg shadow-md w-fit text-md sm:text-xl">
                    <p class="p-1 px-2 text-xs bg-yellow-500 rounded-full w-fit">Warning</p>
                    <p style="padding-left: 3px;">Kamu Sudah Tidak Absen Pulang {{ count($warn) }}x</p>
                </div>
            </div>
        @endif
    </div>

    <div class="{{ $kontrak && $kontrak->isPending() ? 'hidden' : '' }} mx-5 mt-5 sm:mx-10">
        @if ($kontrak && $kontrak->isActive())
            {{-- APPROVED --}}
            <div
                class="mx-auto flex max-w-3xl flex-col-reverse gap-4 rounded-xl border border-white/70 bg-gradient-to-br from-white to-green-50 p-4 shadow-lg shadow-slate-700/10 sm:p-5 md:flex-row md:items-center md:justify-between">
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
                            class="rounded-2xl bg-yellow-400 px-4 py-2.5 text-center text-sm font-bold text-slate-900 shadow-sm transition hover:bg-yellow-500">
                            Lihat Detail
                        </a>

                        {{-- <a href="{{ route('form-kontrak-index', ['token' => \Illuminate\Support\Facades\Crypt::encryptString($kontrak?->id)]) }}"
                            class="rounded-2xl bg-green-600 px-4 py-2.5 text-center text-sm font-bold text-white shadow-sm transition hover:bg-green-700">
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
                class="mx-auto flex max-w-3xl flex-col-reverse gap-4 rounded-xl border border-white/70 bg-gradient-to-br from-white to-blue-50 p-4 shadow-lg shadow-slate-700/10 sm:p-5 md:flex-row md:items-center md:justify-between">
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
                class="mx-auto flex max-w-3xl flex-col-reverse gap-4 rounded-xl border border-white/70 bg-gradient-to-br from-white to-blue-50 p-4 shadow-lg shadow-slate-700/10 sm:p-5 md:flex-row md:items-center md:justify-between">
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
