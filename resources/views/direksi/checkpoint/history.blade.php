<x-app-layout>
    <div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-8 lg:px-12">
        <div class="mt-10 mb-9 flex items-center justify-center gap-6">
            <div class="flex w-full items-center justify-between gap-2">
                <a href="{{ route('direksi.cp.history', ['month' => $previousMonth, 'type' => $type, 'filterKerjasama' => $filter]) }}"
                    aria-label="Bulan sebelumnya"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i
                        class="ri-arrow-left-s-line text-xl"></i></a>
                <div class="flex flex-col items-center text-center">
                    <p class="text-[11px] font-bold uppercase tracking-[.18em] text-sky-600">Direksi Check Point</p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                        {{ $start->translatedFormat('F Y') }}</h1>
                    <p class="mt-2 hidden text-sm text-slate-500 sm:block">Pilih tanggal untuk melihat riwayat
                        pekerjaan.</p>
                </div>
                <a href="{{ route('direksi.cp.history', ['month' => $nextMonth, 'type' => $type, 'filterKerjasama' => $filter]) }}"
                    aria-label="Bulan berikutnya"
                    class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-300 hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i
                        class="ri-arrow-right-s-line text-xl"></i></a>
            </div>
        </div>

        <section
            class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] sm:p-7">
            <form action="{{ route('direksi.cp.history') }}" method="GET"
                class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <select name="type"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:border-sky-300 focus:outline-none">
                    <option value="dikerjakan" {{ $type === 'dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                    <option value="rencana" {{ $type === 'rencana' ? 'selected' : '' }}>Rencana</option>
                </select>
                <select name="filterKerjasama"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:border-sky-300 focus:outline-none">
                    <option value="">Semua Kerja Sama</option>
                    @foreach ($kerjasama as $item)
                        <option value="{{ $item->id }}"
                            {{ (string) $filter === (string) $item->id ? 'selected' : '' }}>
                            {{ $item->client->name ?? '-' }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="month" value="{{ $start->format('Y-m') }}"><button
                    class="h-11 rounded-xl bg-sky-500 px-5 text-sm font-bold text-white transition hover:bg-sky-600">Tampilkan</button>
            </form>
        </section>

        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.04),0_12px_32px_-16px_rgba(15,23,42,.12)] sm:p-7">
            <div
                class="grid grid-cols-7 gap-1.5 pb-3 text-center text-[11px] font-semibold uppercase tracking-[.12em] text-slate-400">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                    <div>{{ $day }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 gap-1.5 overflow-hidden">
                @php
                    $daysBefore = $start->dayOfWeekIso - 1;
                    $daysInMonth = $start->daysInMonth;
                    $daysAfter = (7 - (($daysBefore + $daysInMonth) % 7)) % 7;
                    $nextStart = $start->copy()->addMonth()->startOfMonth();
                @endphp
                @for ($i = $daysBefore; $i > 0; $i--)
                    @php($date = $start->copy()->subDays($i))<span
                        class="flex min-h-[74px] flex-col justify-between rounded-xl border border-slate-100 bg-slate-50/60 p-2.5 text-slate-300"><span
                            class="text-sm">{{ $date->day }}</span></span>
                @endfor
                @foreach ($calendar as $index => $item)
                    @php($date = $item['date']) @php($state = $date->isWeekend() ? 'border-transparent bg-rose-500 text-white' : ($item['hasData'] ? 'border-transparent bg-emerald-500 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-sky-300'))
                    <a href="{{ $item['hasData'] && $item['recordId'] ? route('direksi.cp.history.show', $item['recordId']) : route('direksi.cp.history', ['month' => $start->format('Y-m'), 'type' => $type, 'filterKerjasama' => $filter]) }}"
                        style="animation-delay: {{ min($index * 12, 320) }}ms"
                        class="reveal-cell group relative flex min-h-[74px] flex-col justify-between overflow-hidden rounded-xl border p-2.5 font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_24px_-12px_rgba(15,23,42,.22)] {{ $state }} {{ $date->isToday() ? 'ring-2 ring-sky-400 ring-offset-2' : '' }}"><span
                            class="text-sm">{{ $date->day }}</span>
                        @if ($item['hasData'])
                            <i class="ri-check-line text-[13px]"></i>
                        @elseif ($date->isWeekend())
                            <span class="hidden text-[10px] uppercase sm:block">Libur</span>
                        @endif
                    </a>
                @endforeach
                @for ($i = 1; $i <= $daysAfter; $i++)
                    @php($date = $nextStart->copy()->addDays($i - 1))<span
                        class="flex min-h-[74px] flex-col justify-between rounded-xl border border-slate-100 bg-slate-50/60 p-2.5 text-slate-300"><span
                            class="text-sm">{{ $date->day }}</span></span>
                @endfor
            </div>
        </section>
        <div
            class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-xs text-slate-500">
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-emerald-500"></span>Ada
                riwayat</span><span class="flex items-center gap-2"><span
                    class="h-3 w-3 rounded-[4px] border border-slate-200 bg-white"></span>Belum diisi</span><span
                class="flex items-center gap-2"><span class="h-3 w-3 rounded-[4px] bg-rose-500"></span>Sabtu &amp;
                Minggu</span></div>
    </div>
</x-app-layout>
