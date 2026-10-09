<x-mitra-layout title="Dashboard Kehadiran">
    @php
        $totalKehadiran = $jumlahKehadiran ?? collect($absensiList ?? [])->pluck('user_id')->filter()->unique()->count();
        $totalHariIni = $jumlahAbsensiHariIni ?? 0;
        $labelPeriode = $periodeLabel ?? 'Hari Ini';
        $currentPeriode = $periode ?? 'today';
    @endphp

    <div class="p-6 rounded-3xl mitra-panel mitra-mobile-card">
        <div class="flex flex-col justify-between gap-5 md:flex-row md:items-center">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] mitra-accent">Data Kehadiran Karyawan</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight mitra-text-strong">{{ $totalKehadiran }} Karyawan Hadir</h1>
                <p class="mt-1 text-sm mitra-text-soft">
                    Periode: <span class="font-semibold mitra-text-strong">{{ $labelPeriode }}</span>
                    <span class="mx-2 mitra-text-muted">|</span>
                    Hadir Hari Ini: <span class="font-semibold mitra-text-strong">{{ $totalHariIni }}</span>
                </p>
            </div>
            <div class="w-full md:w-auto">
                <div class="flex w-full flex-col gap-1 p-1 rounded-xl sm:inline-flex sm:flex-row md:w-auto mitra-panel-soft">
                    <a href="{{ route('mitra_absensi', ['periode' => 'today']) }}"
                       class="inline-flex items-center justify-center flex-1 px-4 py-2 text-sm font-semibold transition rounded-lg md:flex-none {{ $currentPeriode === 'today' ? 'bg-blue-500 text-white shadow-xs' : 'mitra-text-soft hover:text-white' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('mitra_absensi', ['periode' => '30days']) }}"
                       class="inline-flex items-center justify-center flex-1 px-4 py-2 text-sm font-semibold transition rounded-lg md:flex-none {{ $currentPeriode === '30days' ? 'bg-blue-500 text-white shadow-xs' : 'mitra-text-soft hover:text-white' }}">
                        30 Hari Terakhir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl mitra-panel-soft">
        <div class="p-4 border-b border-slate-600/50">
            <h2 class="text-xs font-black tracking-[0.3em] uppercase mitra-section-title">Daftar Kehadiran {{ $labelPeriode }}</h2>
        </div>
        <div class="p-4">
            @if($absensiList->isEmpty())
                <p class="text-sm italic mitra-empty-state">Belum ada data kehadiran pada periode ini.</p>
            @else
                <div class="space-y-3 md:hidden" data-viewport-content="mobile">
                    @foreach($absensiList as $absen)
                        <article class="p-4 mitra-mobile-list-card">
                            <div class="flex items-start gap-3">
                                @if($absen->image)
                                    <img
                                        data-responsive-src="{{ asset('storage/images/' . $absen->image) }}"
                                        alt="Foto {{ $absen->user?->nama_lengkap ?? 'Karyawan' }}"
                                        class="object-cover w-12 h-12 rounded-full"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="low"
                                    >
                                @else
                                    <div class="flex items-center justify-center w-12 h-12 text-xs font-bold rounded-full mitra-avatar-fallback">
                                        {{ strtoupper(substr($absen->user?->nama_lengkap ?? 'K', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold mitra-text-strong">{{ capitalizeWords($absen->user?->nama_lengkap ?? '-') }}</p>
                                    <p class="mt-1 text-sm mitra-text-soft">{{ $absen->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Shift</p>
                                    <p class="mt-1 mitra-text-strong">{{ $absen->shift?->shift_name ?? '-' }}</p>
                                    <p class="text-xs mitra-text-muted">{{ $absen->shift?->jam_start ?? '-' }} - {{ \Carbon\Carbon::parse($absen->shift?->jam_end ?? '00:00:00')->subHour()->format('H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Waktu Hadir</p>
                                    <p class="mt-1 mitra-text-strong">{{ $absen->created_at->format('H:i') }} - {{ \Carbon\Carbon::parse($absen->absensi_type_pulang)->format('H:i') }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Keterangan</p>
                                    <div class="mt-1 text-sm">
                                        @php
                                            $keterangan = strtolower(trim((string) ($absen->keterangan ?? 'hadir')));
                                            $isTerlambat = $keterangan === 'telat';
                                            $durasiTerlambat = '';

                                            if ($isTerlambat) {
                                                $jamAbs = $absen->absensi_type_masuk ?? $absen->created_at?->format('H:i:s') ?? '00:00:00';
                                                $jamStr = $absen->shift?->jam_start ?? '00:00:00';
                                                $formatAbs = strlen($jamAbs) === 5 ? 'H:i' : 'H:i:s';
                                                $formatStr = strlen($jamStr) === 5 ? 'H:i' : 'H:i:s';

                                                try {
                                                    $jAbs = \Carbon\Carbon::createFromFormat($formatAbs, $jamAbs);
                                                    $jJad = \Carbon\Carbon::createFromFormat($formatStr, $jamStr);
                                                    $jDiff = $jAbs->diff($jJad);

                                                    if ($jDiff->h > 0) $durasiTerlambat .= $jDiff->format('%h Jam ');
                                                    if ($jDiff->i > 0) $durasiTerlambat .= $jDiff->format('%i Menit ');
                                                    if ($jDiff->s > 0) $durasiTerlambat .= $jDiff->format('%s Detik');

                                                    $durasiTerlambat = trim($durasiTerlambat) ?: '0 Detik';
                                                } catch (\Throwable $th) {
                                                    $durasiTerlambat = '';
                                                }
                                            }
                                        @endphp
                                        @if($isTerlambat)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold text-red-200 bg-red-600 rounded-md">Terlambat</span>
                                            @if($durasiTerlambat !== '')
                                                <p class="mt-1 text-xs text-red-300">{{ $durasiTerlambat }}</p>
                                            @endif
                                        @else
                                            <p class="mitra-text-strong">{{ $absen->keterangan ?? 'Hadir' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('mitra-lihatMap', $absen->id) }}" class="mitra-link-accent hover:underline">Lihat Lokasi</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="hidden -mx-4 px-4 md:block md:mx-0 md:px-0 mitra-table-wrap" data-viewport-content="desktop">
                    <table class="w-full text-sm text-left mitra-table mitra-mobile-table-wide">
                        <thead class="text-xs uppercase bg-slate-800/50">
                            <tr>
                                <th scope="col" class="px-4 py-3">Foto</th>
                                <th scope="col" class="px-4 py-3">Nama</th>
                                <th scope="col" class="px-4 py-3">Shift</th>
                                <th scope="col" class="px-4 py-3">Tanggal</th>
                                <th scope="col" class="px-4 py-3">Waktu Hadir</th>
                                <th scope="col" class="px-4 py-3">Keterangan</th>
                                <th scope="col" class="px-4 py-3">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absensiList as $absen)
                                <tr class="border-b mitra-table-row hover:bg-slate-800/30">
                                    <td class="px-4 py-3">
                                        @if($absen->image)
                                            <img
                                                data-responsive-src="{{ asset('storage/images/' . $absen->image) }}"
                                                alt="Foto {{ $absen->user?->nama_lengkap ?? 'Karyawan' }}"
                                                class="object-cover w-10 h-10 rounded-full"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                            >
                                        @else
                                            <div class="flex items-center justify-center w-10 h-10 text-xs font-bold rounded-full mitra-avatar-fallback">
                                                {{ strtoupper(substr($absen->user?->nama_lengkap ?? 'K', 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium mitra-text-strong">{{ capitalizeWords($absen->user?->nama_lengkap ?? '-') }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium mitra-text-strong">{{ $absen->shift?->shift_name ?? '-' }}</p>
                                        <p class="text-xs text-slate-400">{{ $absen->shift?->jam_start ?? '-' }} - {{ \Carbon\Carbon::parse($absen->shift?->jam_end ?? '00:00:00')->subHour()->format('H:i') }}</p>
                                    </td>
                                    <td class="px-4 py-3">{{ $absen->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $absen->created_at->format('H:i') }} - {{ \Carbon\Carbon::parse($absen->absensi_type_pulang)->format('H:i') }}</td>
                                    <td class="px-4 py-3 capitalize">
                                        @php
                                            $keterangan = strtolower(trim((string) ($absen->keterangan ?? 'hadir')));
                                            $isTerlambat = $keterangan === 'telat';
                                            $durasiTerlambat = '';

                                            if ($isTerlambat) {
                                                $jamAbs = $absen->absensi_type_masuk ?? $absen->created_at?->format('H:i:s') ?? '00:00:00';
                                                $jamStr = $absen->shift?->jam_start ?? '00:00:00';

                                                $formatAbs = strlen($jamAbs) === 5 ? 'H:i' : 'H:i:s';
                                                $formatStr = strlen($jamStr) === 5 ? 'H:i' : 'H:i:s';

                                                try {
                                                    $jAbs = \Carbon\Carbon::createFromFormat($formatAbs, $jamAbs);
                                                    $jJad = \Carbon\Carbon::createFromFormat($formatStr, $jamStr);
                                                    $jDiff = $jAbs->diff($jJad);

                                                    if ($jDiff->h > 0) {
                                                        $durasiTerlambat .= $jDiff->format('%h Jam ');
                                                    }
                                                    if ($jDiff->i > 0) {
                                                        $durasiTerlambat .= $jDiff->format('%i Menit ');
                                                    }
                                                    if ($jDiff->s > 0) {
                                                        $durasiTerlambat .= $jDiff->format('%s Detik');
                                                    }

                                                    $durasiTerlambat = trim($durasiTerlambat);
                                                    if ($durasiTerlambat === '') {
                                                        $durasiTerlambat = '0 Detik';
                                                    }
                                                } catch (\Throwable $th) {
                                                    $durasiTerlambat = '';
                                                }
                                            }
                                        @endphp

                                        @if($isTerlambat)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold text-red-200 bg-red-600 rounded-md">
                                                Terlambat
                                            </span>
                                            @if($durasiTerlambat !== '')
                                                <p class="mt-1 text-xs text-red-300">{{ $durasiTerlambat }}</p>
                                            @endif
                                        @else
                                            {{ $absen->keterangan ?? 'Hadir' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('mitra-lihatMap', $absen->id) }}" class="mitra-link-accent hover:underline">Lihat Lokasi</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let currentSignature = @json($dataSignature ?? 'empty');
            const intervalMs = 10 * 60 * 1000;

            setInterval(async function () {
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.set('check_updates', '1');

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const result = await response.json();
                    if (result.signature && result.signature !== currentSignature) {
                        window.location.reload();
                    }
                } catch (error) {
                    // Silent fail: auto-check akan mencoba lagi di interval berikutnya.
                }
            }, intervalMs);
        });
    </script>
</x-mitra-layout>
