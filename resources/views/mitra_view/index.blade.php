<x-mitra-layout title="Dashboard Mitra" max-width="max-w-[1400px]">
    @php
        $mitra = $mitra ?? [];
        $seri = $mitra['seri'] ?? ['harian' => [], 'mingguan' => [], 'bulanan' => []];
        $distribusi = $mitra['distribusi'] ?? [];
        $ringkasan = $mitra['ringkasan'] ?? [];
        $personelTerbaru = $mitra['personelTerbaru'] ?? collect();
        $persenPengajuan = $mitra['persenPengajuan'] ?? [];
        $totalPersonel = (int) ($mitra['totalPersonel'] ?? 0);
        $hadirHariIni = (int) ($mitra['hadirHariIni'] ?? 0);
        $persenKehadiran = (float) ($mitra['persenKehadiran'] ?? 0);
        $deltaKehadiran = (float) ($mitra['deltaKehadiran'] ?? 0);
        $izinMenunggu = (int) ($mitra['izinMenunggu'] ?? 0);
        $lemburBerjalan = (int) ($mitra['lemburBerjalan'] ?? 0);
        $izinHariIni = (int) ($mitra['izinHariIni'] ?? 0);
        $terlambatHariIni = (int) ($mitra['terlambatHariIni'] ?? 0);
        $totalDistribusi = array_sum(array_column($distribusi, 'value'));
        $belumAbsen = (int) ($distribusi[3]['value'] ?? 0);
        $kellingDonut = 2 * M_PI * 48;
        $pergeseran = 0.0;
        $segmenDonut = [];
        foreach ($distribusi as $item) {
            $panjang = $totalDistribusi > 0 ? ($item['value'] / $totalDistribusi) * $kellingDonut : 0;
            $segmenDonut[] = [
                'label' => $item['label'],
                'value' => $item['value'],
                'persen' => $item['persen'],
                'warna' => $item['warna'],
                'panjang' => $panjang,
                'pergeseran' => $pergeseran,
            ];
            $pergeseran += $panjang;
        }
        $tabs = [
            ['key' => 'harian', 'label' => 'Hari', 'catatan' => 'Tujuh hari terakhir'],
            ['key' => 'mingguan', 'label' => 'Minggu', 'catatan' => 'Rata-rata harian per minggu'],
            ['key' => 'bulanan', 'label' => 'Bulan', 'catatan' => 'Rata-rata harian per bulan'],
        ];
        $jam = (int) \Carbon\Carbon::now()->format('H');
        $sapaan = $jam < 12 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
    @endphp

    {{-- Kepala halaman --}}
    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-medium tracking-wide uppercase text-slate-500">
                {{ $mitra['workspace'] ?? 'Portal Mitra' }}
            </p>
            <h2 class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900">
                {{ $sapaan }}, {{ $mitra['namaMitra'] ?? 'Mitra' }}
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Ringkasan kehadiran dan aktivitas personel per {{ $mitra['periodeLabel'] ?? now()->locale('id')->isoFormat('D MMMM Y') }}.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium bg-white border rounded-xl border-slate-100 text-slate-600">
                <i class="ri-calendar-line"></i>
                {{ $mitra['periodeLabel'] ?? now()->locale('id')->isoFormat('D MMMM Y') }}
            </span>
            <a href="{{ route('mitra_laporan') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-xl bg-[var(--mitra-accent)] hover:bg-[var(--mitra-accent-strong)]">
                <i class="ri-add-line"></i>
                Catat Aktivitas
            </a>
        </div>
    </section>

    @if($totalPersonel === 0)
        <div class="p-6 mt-4 bg-white border rounded-2xl border-slate-100">
            <p class="text-sm font-semibold text-slate-900">Belum ada personel terdaftar</p>
            <p class="mt-1 text-sm text-slate-600">
                Statistik kehadiran akan muncul setelah personel ditambahkan ke workspace ini.
            </p>
            <a href="{{ route('mitra_user') }}" class="inline-flex items-center gap-1 mt-3 text-sm mitra-link-accent">
                Buka data personel
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    @else
        {{-- Baris KPI --}}
        <section class="grid gap-4 mt-4 md:grid-cols-3" aria-label="Metrik utama hari ini">
            <article class="p-6 bg-white border rounded-2xl border-slate-100">
                <p class="text-sm font-medium text-slate-500">Kehadiran hari ini</p>
                <div class="flex items-end justify-between gap-4 mt-3">
                    <div class="min-w-0">
                        <p class="text-3xl font-bold leading-none tracking-tight text-slate-900 tabular-nums">
                            {{ number_format($persenKehadiran, 1, ',', '.') }}<span class="text-xl font-semibold">%</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ $hadirHariIni }} dari {{ $totalPersonel }} personel</p>
                    </div>
                    <div class="flex items-end h-12 gap-1" aria-hidden="true">
                        @foreach($seri['harian'] as $titik)
                            <span class="w-1.5 rounded-full" style="height: {{ max(6, $titik['tinggi']) }}%; background: {{ $titik['puncak'] ? 'var(--mitra-accent)' : '#e2e8f0' }};"></span>
                        @endforeach
                    </div>
                </div>
                <p class="flex items-center gap-1.5 mt-4 text-xs font-semibold {{ $deltaKehadiran > 0 ? 'text-emerald-700' : ($deltaKehadiran < 0 ? 'text-rose-700' : 'text-slate-600') }}">
                    @if($deltaKehadiran > 0)
                        <i class="ri-arrow-up-line"></i>
                        +{{ number_format($deltaKehadiran, 1, ',', '.') }} poin vs kemarin
                    @elseif($deltaKehadiran < 0)
                        <i class="ri-arrow-down-line"></i>
                        {{ number_format($deltaKehadiran, 1, ',', '.') }} poin vs kemarin
                    @else
                        <i class="ri-subtract-line"></i>
                        Sama dengan kemarin
                    @endif
                    <span class="ml-auto text-xs font-normal text-slate-500">7 hari</span>
                </p>
            </article>

            <article class="p-6 bg-white border rounded-2xl border-slate-100">
                <p class="text-sm font-medium text-slate-500">Personel aktif</p>
                <div class="flex items-end justify-between gap-4 mt-3">
                    <div class="min-w-0">
                        <p class="text-3xl font-bold leading-none tracking-tight text-slate-900 tabular-nums">
                            {{ number_format($totalPersonel, 0, ',', '.') }}<span class="ml-1 text-sm font-semibold text-slate-500">staf</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ $hadirHariIni }} sudah absen hari ini</p>
                    </div>
                    <div class="flex items-end h-12 gap-1" aria-hidden="true">
                        @foreach($seri['mingguan'] as $titik)
                            <span class="w-1.5 rounded-full" style="height: {{ max(6, $titik['tinggi']) }}%; background: {{ $titik['puncak'] ? 'var(--mitra-success)' : '#e2e8f0' }};"></span>
                        @endforeach
                    </div>
                </div>
                <p class="flex items-center gap-1.5 mt-4 text-xs font-semibold text-slate-500">
                    <i class="ri-user-unfollow-line text-[13px]"></i>
                    {{ $belumAbsen }} personel belum absen
                    <span class="ml-auto text-xs font-normal text-slate-500">4 minggu</span>
                </p>
            </article>

            <article class="p-6 bg-white border rounded-2xl border-slate-100">
                <p class="text-sm font-medium text-slate-500">Pengajuan menunggu</p>
                <div class="flex items-end justify-between gap-4 mt-3">
                    <div class="min-w-0">
                        <p class="text-3xl font-bold leading-none tracking-tight text-slate-900 tabular-nums">
                            {{ number_format($izinMenunggu, 0, ',', '.') }}<span class="ml-1 text-sm font-semibold text-slate-500">izin</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ $lemburBerjalan }} lembur berjalan hari ini</p>
                    </div>
                    <div class="flex items-end h-12 gap-1" aria-hidden="true">
                        @foreach($persenPengajuan as $index => $tinggi)
                            @php $puncakPengajuan = $tinggi === max($persenPengajuan ?: [0]); @endphp
                            <span class="w-1.5 rounded-full" style="height: {{ max(6, $tinggi) }}%; background: {{ $puncakPengajuan ? 'var(--mitra-warning)' : '#e2e8f0' }};"></span>
                        @endforeach
                    </div>
                </div>
                <p class="flex items-center gap-1.5 mt-4 text-xs font-semibold text-amber-700">
                    <i class="ri-time-line"></i>
                    {{ $izinHariIni }} pengajuan izin hari ini
                    <span class="ml-auto text-xs font-normal text-slate-500">7 hari</span>
                </p>
            </article>
        </section>

        {{-- Banner pengajuan dan personel terbaru --}}
        <section class="p-5 mt-4 bg-white border rounded-2xl border-slate-100">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start min-w-0 gap-3">
                    <span class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-amber-700 rounded-xl bg-amber-50">
                        <i class="text-lg ri-error-warning-line"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900">
                            {{ number_format($izinMenunggu, 0, ',', '.') }} pengajuan izin dan lembur menunggu persetujuan
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $lemburBerjalan }} lembur masih berjalan, {{ $terlambatHariIni }} personel tercatat terlambat hari ini.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 lg:justify-end">
                    @if($personelTerbaru->isNotEmpty())
                        <div class="flex items-center min-w-0 gap-3">
                            <div class="flex -space-x-2.5" role="list" aria-label="Personel yang baru melakukan absensi">
                                @foreach($personelTerbaru as $personel)
                                    @php
                                        $inisial = strtoupper(mb_substr(trim($personel['nama']), 0, 1));
                                    @endphp
                                    <span role="listitem"
                                          class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold bg-white border rounded-full border-slate-200 text-slate-600"
                                          title="{{ $personel['nama'] }} - absen {{ $personel['jam'] }}">
                                        {{ $inisial }}
                                    </span>
                                @endforeach
                            </div>
                            <p class="hidden text-xs text-slate-500 sm:block">
                                Absen terakhir {{ $personelTerbaru->first()['jam'] }}
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-slate-500">Belum ada absensi hari ini</p>
                    @endif
                    <a href="{{ route('mitra_izin') }}" class="inline-flex items-center gap-1 text-sm font-semibold mitra-link-accent">
                        Lihat semua
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- Grafik tren dan distribusi --}}
    <section class="grid gap-4 mt-4 lg:grid-cols-12" aria-label="Analitik kehadiran">
        <article class="p-6 bg-white border rounded-2xl border-slate-100 lg:col-span-7"
                 x-data="{ tab: 'harian' }">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Tren Kehadiran &amp; Aktivitas</h3>
                    <p class="mt-1 text-xs text-slate-500">Jumlah personel yang absen, dihitung sekali per hari.</p>
                </div>
                <div class="flex items-center gap-1 p-1 rounded-full bg-slate-100" role="tablist" aria-label="Periode grafik kehadiran">
                    @foreach($tabs as $tab)
                        <button type="button"
                                role="tab"
                                id="tab-{{ $tab['key'] }}"
                                aria-controls="panel-{{ $tab['key'] }}"
                                :aria-selected="tab === '{{ $tab['key'] }}' ? 'true' : 'false'"
                                :tabindex="tab === '{{ $tab['key'] }}' ? 0 : -1"
                                @click="tab = '{{ $tab['key'] }}'"
                                :class="tab === '{{ $tab['key'] }}' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1.5 text-xs font-semibold transition rounded-full">
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            @foreach($tabs as $tab)
                @php
                    $titik = $seri[$tab['key']] ?? [];
                    $jumlahTitik = max(count($titik), 1);
                    $totalTitik = array_sum(array_column($titik, 'value'));
                @endphp
                <div role="tabpanel"
                     id="panel-{{ $tab['key'] }}"
                     aria-labelledby="tab-{{ $tab['key'] }}"
                     x-show="tab === '{{ $tab['key'] }}'"
                     x-cloak>
                    <div class="flex items-end justify-between gap-3 mt-6">
                        <div>
                            <p class="text-2xl font-bold tracking-tight text-slate-900 tabular-nums">
                                {{ $hadirHariIni }}<span class="ml-1 text-sm font-semibold text-slate-500">personel hadir hari ini</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-500">{{ $tab['catatan'] }}</p>
                        </div>
                        <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-800">
                            {{ number_format($persenKehadiran, 1, ',', '.') }}% dari {{ $totalPersonel }} personel
                        </span>
                    </div>

                    @if($totalTitik === 0)
                        <div class="flex flex-col items-center justify-center h-56 gap-2 mt-4 text-center border border-dashed rounded-xl border-slate-200 bg-slate-50">
                            <i class="text-2xl ri-bar-chart-2-line text-slate-400"></i>
                            <p class="text-sm font-medium text-slate-700">Belum ada absensi pada periode ini</p>
                            <p class="text-xs text-slate-500">Data akan muncul setelah personel melakukan absensi.</p>
                        </div>
                    @else
                        <div class="grid items-end gap-2 mt-8 h-44"
                             style="grid-template-columns: repeat({{ $jumlahTitik }}, minmax(0, 1fr));"
                             role="img"
                             aria-label="Grafik kehadiran {{ strtolower($tab['label']) }}: {{ collect($titik)->map(fn ($t) => $t['keterangan'] . ' ' . $t['value'] . ' personel')->implode(', ') }}">
                            @foreach($titik as $bar)
                                <div class="relative flex items-end justify-center h-full">
                                    <div class="w-full rounded-t-lg transition-[height] duration-200"
                                         style="height: {{ $bar['tinggi'] }}%; background: {{ $bar['puncak'] ? 'var(--mitra-accent)' : '#e8eef7' }};"></div>
                                    @if($bar['puncak'])
                                        <span class="absolute z-10 px-2 py-1 text-[10px] font-semibold text-white -translate-x-1/2 rounded-lg left-1/2 whitespace-nowrap bg-slate-900"
                                              style="bottom: calc({{ $bar['tinggi'] }}% + 0.5rem);">
                                            Puncak {{ number_format($bar['persenPersonel'], 1, ',', '.') }}%
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="grid gap-2 mt-2"
                             style="grid-template-columns: repeat({{ $jumlahTitik }}, minmax(0, 1fr));">
                            @foreach($titik as $bar)
                                <span class="text-center text-[11px] font-medium text-slate-500">{{ $bar['label'] }}</span>
                            @endforeach
                        </div>
                        <p class="sr-only">
                            {{ collect($titik)->map(fn ($t) => $t['keterangan'] . ': ' . $t['value'] . ' personel')->implode('. ') }}.
                        </p>
                    @endif
                </div>
            @endforeach
        </article>

        <div class="space-y-4 lg:col-span-5">
            <article class="p-6 bg-white border rounded-2xl border-slate-100">
                <h3 class="text-base font-semibold text-slate-900">Distribusi Status Karyawan</h3>
                <p class="mt-1 text-xs text-slate-500">Komposisi personel hari ini.</p>

                <div class="flex flex-col items-center gap-6 mt-5 sm:flex-row sm:items-center">
                    <div class="relative flex-shrink-0 w-40 h-40"
                         role="img"
                         aria-label="Distribusi status: {{ collect($distribusi)->map(fn ($d) => $d['label'] . ' ' . $d['value'] . ' personel')->implode(', ') }}">
                        <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                            <circle cx="60" cy="60" r="48" fill="none" stroke="#eef2f7" stroke-width="14"></circle>
                            @if($totalDistribusi > 0)
                                @foreach($segmenDonut as $segmen)
                                    @continue($segmen['value'] <= 0)
                                    <circle cx="60"
                                            cy="60"
                                            r="48"
                                            fill="none"
                                            stroke="{{ $segmen['warna'] }}"
                                            stroke-width="14"
                                            stroke-linecap="butt"
                                            stroke-dasharray="{{ $segmen['panjang'] }} {{ $kellingDonut - $segmen['panjang'] }}"
                                            stroke-dashoffset="{{ -$segmen['pergeseran'] }}"></circle>
                                @endforeach
                            @endif
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p class="text-2xl font-bold leading-none tracking-tight text-slate-900 tabular-nums">
                                {{ number_format($persenKehadiran, 0, ',', '.') }}%
                            </p>
                            <p class="mt-1 text-[11px] font-medium text-slate-500">kehadiran</p>
                        </div>
                    </div>

                    <ul class="w-full space-y-3">
                        @foreach($distribusi as $item)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full" style="background: {{ $item['warna'] }};"></span>
                                <span class="min-w-0 text-slate-600">{{ $item['label'] }}</span>
                                <span class="ml-auto font-semibold text-slate-900 tabular-nums">{{ $item['value'] }}</span>
                                <span class="w-12 text-right text-xs text-slate-500 tabular-nums">{{ number_format($item['persen'], 0, ',', '.') }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </article>

            <article class="p-6 bg-white border rounded-2xl border-slate-100">
                <h3 class="text-base font-semibold text-slate-900">Ringkasan Bulanan</h3>
                <p class="mt-1 text-xs text-slate-500">Rekap {{ now()->locale('id')->isoFormat('MMMM Y') }}.</p>

                <div class="grid grid-cols-3 gap-3 mt-5 divide-x divide-slate-100">
                    <div class="pr-3">
                        <p class="text-[11px] font-medium text-slate-500">Jam lembur</p>
                        <p class="mt-1.5 text-lg font-bold leading-none text-slate-900 tabular-nums">
                            {{ number_format((float) ($ringkasan['jamLembur'] ?? 0), 1, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500">jam tercatat</p>
                    </div>
                    <div class="px-3">
                        <p class="text-[11px] font-medium text-slate-500">Disiplin</p>
                        <p class="mt-1.5 text-lg font-bold leading-none text-slate-900 tabular-nums">
                            {{ is_null($ringkasan['disiplin'] ?? null) ? '-' : number_format($ringkasan['disiplin'], 1, ',', '.') . '%' }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500">tanpa telat</p>
                    </div>
                    <div class="pl-3">
                        <p class="text-[11px] font-medium text-slate-500">Izin disetujui</p>
                        <p class="mt-1.5 text-lg font-bold leading-none text-slate-900 tabular-nums">
                            {{ number_format((int) ($ringkasan['izinDisetujui'] ?? 0), 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500">dari {{ number_format((int) ($ringkasan['izinBulanIni'] ?? 0), 0, ',', '.') }} pengajuan</p>
                    </div>
                </div>
            </article>
        </div>
    </section>
</x-mitra-layout>
