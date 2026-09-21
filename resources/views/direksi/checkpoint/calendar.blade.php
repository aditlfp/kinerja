<x-app-layout>
    @section('title', 'Kalender Checkpoint')
    <div class="w-full max-w-5xl px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="mt-10 p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-sky-50 text-xl text-sky-600"><i
                            class="ri-user-line"></i></span>
                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold capitalize text-gray-900 sm:text-xl">
                            {{ strtolower($employee->nama_lengkap) }}</h1>
                        <p class="mt-0.5 truncate text-xs text-gray-500">{{ $employee->jabatan->nama_jabatan ?? '-' }}
                            &middot; {{ $employee->divisi->name ?? '-' }} &middot;
                            {{ $employee->kerjasama->client->name ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('direksi.cp.index') }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50"><i
                        class="mr-1.5 ri-arrow-left-line"></i> Kembali</a>
            </div>
        </section>
        <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
            <div class="mb-4 flex items-center justify-between">
                <a href="{{ route('direksi.cp.calendar', ['user' => $employee->id, 'month' => $previousMonth, 'type' => $type]) }}"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-gray-200 text-gray-500 hover:border-purple-300 hover:text-purple-600"
                    aria-label="Bulan sebelumnya"><i class="text-xl ri-arrow-left-s-line"></i></a>
                <div class="text-center">
                    <h2 class="text-lg font-bold text-gray-900">{{ $start->translatedFormat('F Y') }}</h2>
                    <p class="text-xs text-gray-500">{{ $totalRecords }} Bukti Pekerjaan</p>
                </div>
                <a href="{{ route('direksi.cp.calendar', ['user' => $employee->id, 'month' => $nextMonth, 'type' => $type]) }}"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-gray-200 text-gray-500 hover:border-purple-300 hover:text-purple-600"
                    aria-label="Bulan berikutnya"><i class="text-xl ri-arrow-right-s-line"></i></a>
            </div>
            <div
                class="grid grid-cols-7 gap-1.5 pb-3 text-center text-[11px] font-semibold uppercase tracking-[.12em] text-gray-400">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                    <div>{{ $day }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 gap-1.5">
                @php
                    $daysBefore = $start->dayOfWeekIso - 1;
                    $daysInMonth = $start->daysInMonth;
                    $daysAfter = (7 - (($daysBefore + $daysInMonth) % 7)) % 7;
                    $nextStart = $start->copy()->addMonth()->startOfMonth();
                @endphp
                @for ($i = $daysBefore; $i > 0; $i--)
                    @php $date = $start->copy()->subDays($i); @endphp
                    <span
                        class="flex min-h-[64px] flex-col justify-between overflow-hidden rounded-xl border border-gray-100 bg-gray-50/60 p-2.5 text-gray-300 sm:min-h-[74px] mt-2"><span
                            class="text-sm leading-none overflow-hidden">{{ $date->day }}</span></span>
                @endfor
                @foreach ($calendar as $index => $item)
                    @php
                        $date = $item['date'];
                        $state = $item['hasData']
                            ? 'border-transparent bg-emerald-500 text-white hover:bg-emerald-600 mt-2'
                            : ($date->isWeekend()
                                ? 'border-rose-100 bg-rose-50 text-rose-400 mt-2'
                                : 'border-gray-200 bg-white text-gray-700 mt-2');
                    @endphp
                    @if ($item['hasData'] && $item['recordId'])
                        <a href="{{ $item['hasData'] && $item['recordId'] ? route('direksi.cp.history.show', $item['recordId']) : route('direksi.cp.history', ['month' => $start->format('Y-m'), 'type' => $type, 'filterKerjasama' => $filter]) }}"
                            style="animation-delay: {{ min($index * 12, 320) }}ms"
                            class="reveal-cell group relative flex min-h-[74px] flex-col justify-between overflow-hidden rounded-xl border p-2.5 font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] {{ $state }} {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-2' : '' }}"><span
                                class="text-sm">{{ $date->day }}</span><i
                                class="text-[13px] ri-check-line opacity-90"></i></a>
                    @else
                        <span title="{{ $date->translatedFormat('l, d F Y') }}"
                            class="flex min-h-[64px] flex-col justify-between overflow-hidden rounded-xl border p-2.5 font-medium {{ $state }} sm:min-h-[74px]"><span
                                class="text-sm leading-none overflow-hidden">{{ $date->day }}</span>
                            @if ($date->isWeekend())
                                <span
                                    class="hidden text-[10px] font-semibold uppercase opacity-85 sm:block">Libur</span>
                            @endif
                        </span>
                    @endif
                @endforeach
                @for ($i = 1; $i <= $daysAfter; $i++)
                    @php $date = $nextStart->copy()->addDays($i - 1); @endphp
                    <span
                        class="flex min-h-[64px] flex-col justify-between overflow-hidden rounded-xl border border-gray-100 bg-gray-50/60 p-2.5 text-gray-300 sm:min-h-[74px] mt-2"><span
                            class="text-sm leading-none overflow-hidden">{{ $date->day }}</span></span>
                @endfor
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500">
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-emerald-500"></span>Ada
                    checkpoint</span>
                <span class="flex items-center gap-2"><span
                        class="h-3 w-3 rounded-[4px] border border-gray-200 bg-white"></span>Belum diisi</span>
                <span class="flex items-center gap-2"><span
                        class="h-3 w-3 rounded-[4px] bg-rose-50 ring-1 ring-rose-100"></span>Sabtu &amp; Minggu</span>
            </div>
        </section>
    </div>
</x-app-layout>
