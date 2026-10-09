<x-app-layout>
    <x-main-div>
        <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                    <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
                </div>
                <div class="rounded-2xl bg-white py-6 shadow-xs ring-1 ring-slate-900/5 sm:py-10">
                    <p class="border-l-4 border-sky-500 text-lg font-bold text-center uppercase sm:text-2xl">Absen Sholat, Data Kehadiran</p>
                    <div class="px-3 py-4">

            <!-- Dashboard/Stats Summary -->
            @php
                $totalUsers = $user->count();
                $absenSubuh = $absen->where('subuh', 1)->count();
                $absenDzuhur = $absen->where('dzuhur', 1)->count();
                $absenAshar = $absen->where('asar', 1)->count();
                $absenMaghrib = $absen->where('maghrib', 1)->count();
                $absenIsya = $absen->where('isya', 1)->count();

                $pct = fn($val) => $totalUsers > 0 ? round(($val / $totalUsers) * 100, 1) : 0;
            @endphp

            <section class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                <div class="p-3 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Total</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ $totalUsers }}</p>
                </div>
                <div class="p-3 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Subuh</p>
                    <p class="mt-1 text-xl font-bold text-green-600">{{ $absenSubuh }} <span class="text-[10px] font-normal text-gray-400">({{ $pct($absenSubuh) }}%)</span></p>
                </div>
                <div class="p-3 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Dzuhur</p>
                    <p class="mt-1 text-xl font-bold text-green-600">{{ $absenDzuhur }} <span class="text-[10px] font-normal text-gray-400">({{ $pct($absenDzuhur) }}%)</span></p>
                </div>
                <div class="p-3 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Ashar</p>
                    <p class="mt-1 text-xl font-bold text-green-600">{{ $absenAshar }} <span class="text-[10px] font-normal text-gray-400">({{ $pct($absenAshar) }}%)</span></p>
                </div>
                <div class="p-3 bg-white border border-gray-100 shadow-sm rounded-xl col-span-2 sm:col-span-1">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Maghrib/Isya</p>
                    <p class="mt-1 text-xl font-bold text-green-600">{{ $absenMaghrib + $absenIsya }} <span class="text-[10px] font-normal text-gray-400">({{ $pct($absenMaghrib + $absenIsya) }}%)</span></p>
                </div>
            </section>

            <!-- Attendance Records Table -->
            <section class="overflow-hidden bg-white border border-gray-100 shadow-xs rounded-xl">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Rekap Harian</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Kehadiran sholat seluruh anggota hari ini
                        </p>
                    </div>

                    <span class="px-2.5 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded-full">
                        {{ $absen->count() }} Data
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px]">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-gray-500 bg-gray-50">
                                <th class="px-4 py-3 text-left font-semibold">#</th>
                                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                                <th class="px-3 py-3 text-center font-semibold">Sub</th>
                                <th class="px-3 py-3 text-center font-semibold">Dzu</th>
                                <th class="px-3 py-3 text-center font-semibold">Ash</th>
                                <th class="px-3 py-3 text-center font-semibold">Mag</th>
                                <th class="px-3 py-3 text-center font-semibold">Isya</th>
                                <th class="px-4 py-3 text-center font-semibold">Progress</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($absen as $i => $record)
                                @php
                                    $total =
                                        ($record->subuh ?: 0) +
                                        ($record->dzuhur ?: 0) +
                                        ($record->asar ?: 0) +
                                        ($record->maghrib ?: 0) +
                                        ($record->isya ?: 0);

                                    $pctTotal = ($total / 5) * 100;
                                @endphp

                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $i + 1 }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ capitalizeWords($record->user->nama_lengkap ?? '-') }}
                                        </div>
                                    </td>

                                    @foreach([
                                        $record->subuh,
                                        $record->dzuhur,
                                        $record->asar,
                                        $record->maghrib,
                                        $record->isya,
                                    ] as $status)
                                        <td class="px-3 py-3 text-center">
                                            @if($status)
                                                <span class="inline-flex items-center justify-center w-7 h-7 text-green-700 rounded-full bg-green-50">
                                                    ✓
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-7 h-7 text-red-400 rounded-full bg-red-50">
                                                    ✕
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div
                                                    class="h-full rounded-full bg-green-500"
                                                    style="width: {{ $pctTotal }}%">
                                                </div>
                                            </div>

                                            <span class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                                                {{ $total }}/5
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100">
                                                🕌
                                            </div>

                                            <p class="font-medium text-gray-700">
                                                Belum ada data absen
                                            </p>

                                            <p class="text-xs text-gray-500">
                                                Data kehadiran sholat hari ini belum tersedia.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
                    </div>
                </div>
            </div>
        </div>
    </x-main-div>
</x-app-layout>